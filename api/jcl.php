<?php
// jcl.php – API-Endpunkt zur Ausführung von JCL-Jobs
// Dieser Endpunkt wird vom Frontend (JS-Terminal) über "JCL RUN ..." verwendet.
// Erwartet POST-Request mit: jcl=... (mehrzeiliger JCL-String)

require_once '../classes/JCLInterpreter.php'; // Einbinden der JCL-Verarbeitungslogik

// Neue Instanz des JCL-Interpreters erstellen
$interpreter = new JCLInterpreter();

// Übergebene JCL auswerten und Ergebnis ausgeben
// POST['jcl'] enthält den kompletten JCL-Text (ggf. mit \n ersetzt)
echo $interpreter->run($_POST['jcl'] ?? '');
