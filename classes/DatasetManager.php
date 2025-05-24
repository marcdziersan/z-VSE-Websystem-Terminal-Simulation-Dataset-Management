<?php

/**
 * Klasse zur Verwaltung von Datasets und PDS-Members in einem z/VSE-artigen System.
 * Backend: SQLite-Datenbank
 */
class DatasetManager {
    /** @var PDO $db Die PDO-Datenbankverbindung */
    private PDO $db;

    /**
     * Konstruktor: Initialisiert Datenbankverbindung und Datenstruktur.
     * @param string $dbPath Pfad zur SQLite-Datenbank
     */
    public function __construct(string $dbPath = 'zvse_storage.db') {
        $this->db = new PDO("sqlite:$dbPath");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->initialize();
    }

    /**
     * Initialisiert die Tabellenstruktur in der Datenbank (Datasets und Members).
     */
    private function initialize(): void {
        $this->db->exec("CREATE TABLE IF NOT EXISTS datasets (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE NOT NULL,
            type TEXT CHECK(type IN ('SEQUENTIAL', 'PDS', 'VSAM')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");

        $this->db->exec("CREATE TABLE IF NOT EXISTS pds_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            dataset_id INTEGER,
            member_name TEXT,
            content TEXT,
            FOREIGN KEY (dataset_id) REFERENCES datasets(id)
        );");
    }

    /**
     * Erstellt ein neues Dataset.
     * @param string $name Name des Datasets
     * @param string $type Typ des Datasets (Standard: PDS)
     * @return string Ergebnisnachricht
     */
    public function create(string $name, string $type = 'PDS'): string {
        $stmt = $this->db->prepare("INSERT OR IGNORE INTO datasets (name, type) VALUES (?, ?)");
        $stmt->execute([$name, strtoupper($type)]);
        return "Dataset '$name' erstellt.";
    }

    /**
     * Fügt einem Dataset einen neuen Member hinzu.
     * @param string $dataset Name des Datasets
     * @param string $member  Name des Members
     * @param string $content Inhalt des Members
     * @return string Ergebnisnachricht
     */
    public function addMember(string $dataset, string $member, string $content): string {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$dataset]);
        $ds = $stmt->fetch();
        if (!$ds) return "Dataset '$dataset' nicht gefunden.";

        $stmt = $this->db->prepare("INSERT INTO pds_members (dataset_id, member_name, content) VALUES (?, ?, ?)");
        $stmt->execute([$ds['id'], $member, $content]);
        return "Member '$member' zum Dataset '$dataset' hinzugefügt.";
    }

    /**
     * Liest den Inhalt eines bestimmten Members.
     * @param string $dataset Name des Datasets
     * @param string $member  Name des Members
     * @return string Inhalt oder Fehlermeldung
     */
    public function readMember(string $dataset, string $member): string {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$dataset]);
        $ds = $stmt->fetch();
        if (!$ds) return "Dataset '$dataset' nicht gefunden.";

        $stmt = $this->db->prepare("SELECT content FROM pds_members WHERE dataset_id = ? AND member_name = ?");
        $stmt->execute([$ds['id'], $member]);
        $content = $stmt->fetchColumn();
        return $content ?: "Member '$member' nicht gefunden.";
    }

    /**
     * Löscht einen bestimmten Member aus einem Dataset.
     * @param string $dataset Name des Datasets
     * @param string $member  Name des Members
     * @return string Ergebnisnachricht
     */
    public function deleteMember(string $dataset, string $member): string {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$dataset]);
        $ds = $stmt->fetch();
        if (!$ds) return "Dataset '$dataset' nicht gefunden.";

        $stmt = $this->db->prepare("DELETE FROM pds_members WHERE dataset_id = ? AND member_name = ?");
        $stmt->execute([$ds['id'], $member]);
        return "Member '$member' aus Dataset '$dataset' gelöscht.";
    }

    /**
     * Gibt eine textuelle Liste aller vorhandenen Datasets zurück.
     * @return string Liste als String oder Hinweis, wenn leer
     */
    public function listAll(): string {
        $stmt = $this->db->query("SELECT name, type, created_at FROM datasets");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) return "Keine Datasets gefunden.";

        return implode("\n", array_map(
            fn($r) => "DSN: {$r['name']} ({$r['type']}) - Erstellt am {$r['created_at']}",
            $rows
        ));
    }

    /**
     * Gibt den ersten numerischen Inhalt eines Datasets zurück (für Berechnungen).
     * @param string $dataset Name des Datasets
     * @return float|null Numerischer Wert oder null
     */
    public function getFirstValue(string $dataset): ?float {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$dataset]);
        $ds = $stmt->fetch();
        if (!$ds) return null;

        $stmt = $this->db->prepare("SELECT content FROM pds_members WHERE dataset_id = ? LIMIT 1");
        $stmt->execute([$ds['id']]);
        $val = $stmt->fetchColumn();
        return is_numeric($val) ? (float)$val : null;
    }

    /**
     * Benennt ein Dataset um.
     * @param string $oldName Alter Name
     * @param string $newName Neuer Name
     * @return string Ergebnisnachricht
     */
    public function renameDataset(string $oldName, string $newName): string {
        $stmt = $this->db->prepare("UPDATE datasets SET name = ? WHERE name = ?");
        $stmt->execute([$newName, $oldName]);
        return $stmt->rowCount()
            ? "Dataset '$oldName' wurde in '$newName' umbenannt."
            : "Dataset '$oldName' nicht gefunden.";
    }

    /**
     * Löscht ein Dataset samt aller zugehörigen Member.
     * @param string $name Name des Datasets
     * @return string Ergebnisnachricht
     */
    public function deleteDataset(string $name): string {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$name]);
        $ds = $stmt->fetch();
        if (!$ds) return "Dataset '$name' nicht gefunden.";

        // Zuerst alle Member löschen
        $this->db->prepare("DELETE FROM pds_members WHERE dataset_id = ?")->execute([$ds['id']]);

        // Dann das Dataset selbst löschen
        $this->db->prepare("DELETE FROM datasets WHERE id = ?")->execute([$ds['id']]);

        return "Dataset '$name' und alle zugehörigen Member wurden gelöscht.";
    }

    /**
     * Gibt alle Member eines Datasets zurück (z. B. für PRINT oder LIST).
     * @param string $dataset Name des Datasets
     * @return array Liste von Membern (assoziativ: name + content)
     */
    public function getMembers(string $dataset): array {
        $stmt = $this->db->prepare("SELECT id FROM datasets WHERE name = ?");
        $stmt->execute([$dataset]);
        $ds = $stmt->fetch();
        if (!$ds) return [];

        $stmt = $this->db->prepare("SELECT member_name, content FROM pds_members WHERE dataset_id = ?");
        $stmt->execute([$ds['id']]);
        return $stmt->fetchAll();
    }
}
