<?php
// calc.php – API-Endpunkt für einfache mathematische Berechnungen
// Erwarteter Aufruf: ?cmd=OP A B, z. B. ?cmd=ADD 4 5

require_once '../classes/Calculator.php'; // Einbindung der Rechen-Klasse

// Befehl auslesen (z. B. "ADD 10 5")
$cmd = $_GET['cmd'] ?? ''; // Übergabe über URL, z. B. ?cmd=ADD 10 5

// In Operation + Operanden aufteilen
[$op, $a, $b] = explode(' ', $cmd);

// Umwandlung in Gleitkommazahlen (float)
$a = floatval($a);
$b = floatval($b);

// Auswahl der Rechenoperation
switch (strtoupper($op)) {
    case 'ADD': echo Calculator::add($a, $b); break;  // Addition
    case 'SUB': echo Calculator::sub($a, $b); break;  // Subtraktion
    case 'MUL': echo Calculator::mul($a, $b); break;  // Multiplikation
    case 'DIV': echo Calculator::div($a, $b); break;  // Division (mit 0-Prüfung in Klasse)
    default: echo "Unbekannte Operation";             // Fehlerausgabe
}

/*
GET /api/calc.php?cmd=ADD 10 5   → Ausgabe: 15
GET /api/calc.php?cmd=DIV 8 2    → Ausgabe: 4
GET /api/calc.php?cmd=XYZ 1 2    → Ausgabe: Unbekannte Operation
*/