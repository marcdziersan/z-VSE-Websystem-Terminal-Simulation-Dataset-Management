<?php
// Zentrale API-Router-Datei für das z/VSE-Websystem
// Diese Datei empfängt alle Anfragen vom Frontend (zvse.js)
// und ruft die passenden Methoden aus den OOP-Klassen auf.

// Einbinden der benötigten Klassen
require_once 'classes/DatasetManager.php';   // Für Dataset- und Member-Verwaltung
require_once 'classes/JCLInterpreter.php';   // Für die Ausführung von JCL-Jobs
require_once 'classes/Calculator.php';       // Für einfache Rechenoperationen

// Abrufen der übergebenen Aktion (GET-Parameter)
$action = $_GET['action'] ?? '';

// Instanzen der Klassen erzeugen
$ds = new DatasetManager();      // Dataset-Logik
$jcl = new JCLInterpreter();     // JCL-Interpreter

// Haupt-Switch: verarbeitet die Aktion anhand von ?action=...
switch ($action) {

    // Dataset erstellen (POST: name)
    case 'create_dataset':
        echo $ds->create($_POST['name'] ?? '');
        break;

    // Member hinzufügen (POST: dataset, member, content)
    case 'add_member':
        echo $ds->addMember(
            $_POST['dataset'] ?? '',
            $_POST['member'] ?? '',
            $_POST['content'] ?? ''
        );
        break;

    // Member lesen (GET: dataset, member)
    case 'read_member':
        echo $ds->readMember(
            $_GET['dataset'] ?? '',
            $_GET['member'] ?? ''
        );
        break;

    // Member löschen (GET: dataset, member)
    case 'delete_member':
        echo $ds->deleteMember(
            $_GET['dataset'] ?? '',
            $_GET['member'] ?? ''
        );
        break;

    // Alle Datasets auflisten
    case 'list_all':
        echo $ds->listAll();
        break;

    // JCL-Code ausführen (POST: jcl)
    case 'run_jcl':
        echo $jcl->run($_POST['jcl'] ?? '');
        break;

    // Direkte Rechenoperation (GET: cmd=ADD 4 5)
    case 'calc':
        $cmd = $_GET['cmd'] ?? '';
        [$op, $a, $b] = explode(' ', $cmd); // Zerlege in OP A B
        $a = floatval($a);
        $b = floatval($b);

        // Führe die Rechenoperation aus
        switch (strtoupper($op)) {
            case 'ADD': echo Calculator::add($a, $b); break;
            case 'SUB': echo Calculator::sub($a, $b); break;
            case 'MUL': echo Calculator::mul($a, $b); break;
            case 'DIV': echo Calculator::div($a, $b); break;
            default: echo "Unbekannte Operation";
        }
        break;

    // Standardfall bei unbekannter Aktion
    default:
        echo "Unbekannte Aktion.";
}
