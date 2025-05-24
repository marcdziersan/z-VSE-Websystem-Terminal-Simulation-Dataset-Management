<?php
// Dieses Skript ist die API-Schnittstelle für alle Dataset-Operationen.
// Es wird von JavaScript über fetch() aufgerufen (GET/POST)
// und nutzt die Klasse DatasetManager zur Ausführung der Logik.

require_once '../classes/DatasetManager.php';  // Einbindung der OOP-Datenbankklasse
$ds = new DatasetManager();                    // Instanz der Klasse erstellen

// Ermittlung der Aktion über GET-Parameter
$action = $_GET['action'] ?? '';

// Haupt-Switch zur Verarbeitung der Aktion
switch ($action) {

    // Dataset anlegen (POST: name)
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

    // Dataset umbenennen (POST: old, new)
    case 'rename_dataset':
        echo $ds->renameDataset(
            $_POST['old'] ?? '',
            $_POST['new'] ?? ''
        );
        break;

    // Dataset löschen (GET: name)
    case 'delete_dataset':
        echo $ds->deleteDataset($_GET['name'] ?? '');
        break;

    // Fallback für unbekannte Aktionen
    default:
        echo "Unbekannte Aktion.";
}
