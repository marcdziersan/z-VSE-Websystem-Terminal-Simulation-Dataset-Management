<?php
// JCLInterpreter.php
// Diese Klasse interpretiert und verarbeitet JCL-ähnliche Befehle im z/VSE-System.

require_once 'DatasetManager.php';  // Für Zugriff auf Datasets und Members
require_once 'Calculator.php';      // Für mathematische Operationen (PGM=ADD, etc.)

/**
 * Klasse JCLInterpreter
 * Simuliert die Ausführung von JCL (Job Control Language) ähnlich z/VSE
 */
class JCLInterpreter {
    private DatasetManager $ds; // Referenz zur Datenverwaltung

    /**
     * Konstruktor: Initialisiert die Dataset-Verwaltung
     */
    public function __construct() {
        $this->ds = new DatasetManager();
    }

    /**
     * Führt eine JCL-Jobbeschreibung aus und gibt eine textuelle Ausgabe zurück.
     *
     * @param string $rawJcl Mehrzeiliger JCL-Code
     * @return string Ergebnis der JCL-Ausführung (Protokollausgabe)
     */
    public function run(string $rawJcl): string {
        $lines = explode("\n", $rawJcl);           // JCL-Zeilen extrahieren
        $output = "JCL-Ausführung gestartet:\n";   // Initiale Ausgabe
        $referencedDSNs = [];                      // Gesammelte Datasets (DD DSN=...)

        // 1. Dataset-Referenzen sammeln aus DD-Zeilen
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '//') &&
                stripos($line, 'DD') !== false &&
                stripos($line, 'DSN=') !== false) {

                preg_match('/DSN=([^,]+)/', $line, $matches);
                if ($matches) {
                    $dsn = trim($matches[1], "'\"\\ \t\r\n"); // saubere DSN extrahieren
                    $referencedDSNs[] = $dsn;
                    $output .= "Dataset '$dsn' referenziert.\n";
                }
            }
        }

        // 2. Programme ausführen aus EXEC-Zeilen
        foreach ($lines as $line) {
            $line = trim($line);

            if (str_starts_with($line, '//') && stripos($line, 'EXEC PGM=') !== false) {
                // Programmname extrahieren (PGM=XYZ)
                $pgm = strtoupper(trim(explode('PGM=', $line)[1]));

                // PGM=LISTCAT → Zeigt alle Datasets
                if ($pgm === 'LISTCAT') {
                    $output .= "LISTCAT-Ausgabe:\n" . $this->ds->listAll() . "\n";
                }

                // PGM=PRINT → Gibt Inhalte aller referenzierten Datasets aus
                elseif ($pgm === 'PRINT') {
                    foreach ($referencedDSNs as $dsn) {
                        $members = $this->ds->getMembers($dsn);
                        foreach ($members as $m) {
                            $output .= "[$dsn] {$m['member_name']}:\n{$m['content']}\n";
                        }
                    }
                }

                // PGM=ADD → Addiert Inhalte aus zwei Datasets (numerisch)
                elseif ($pgm === 'ADD') {
                    if (count($referencedDSNs) >= 2) {
                        $a = $this->ds->getFirstValue($referencedDSNs[0]);
                        $b = $this->ds->getFirstValue($referencedDSNs[1]);

                        if ($a !== null && $b !== null) {
                            $result = Calculator::add($a, $b);
                            $output .= "ADD-Ergebnis: $a + $b = $result\n";
                        } else {
                            $output .= "Fehler: Kein numerischer Inhalt in Dataset.\n";
                        }
                    } else {
                        $output .= "Fehler: Zwei Datasets für ADD notwendig.\n";
                    }
                }

                // Unbekanntes Programm
                else {
                    $output .= "Programm '$pgm' wird (symbolisch) ausgeführt.\n";
                }
            }
        }

        return $output;
    }
}
