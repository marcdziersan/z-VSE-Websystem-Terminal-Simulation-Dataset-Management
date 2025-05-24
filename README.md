# z/VSE Websystem – Terminal-Simulation & Dataset-Management

Eine moderne Web-Simulation eines klassischen IBM z/VSE-Systems – inklusive interaktivem Terminal, JCL-Interpreter und SQLite-basiertem Dateisystem.

---

## 🔧 Features

- 🖥️ **Webbasiertes Terminal** (HTML/JS)
- 📂 **Dataset-Verwaltung** (CREATE, LISTCAT, ADD/READ/DELETE MEMBER)
- 📜 **JCL-Ausführung** (PGM=LISTCAT, PGM=PRINT, PGM=ADD)
- ➕ **Arithmetische Operationen** (ADD, SUB, MUL, DIV)
- 🧠 **Modular und objektorientiert in PHP**
- 💾 **Datenhaltung mit SQLite**
- 🧪 Einfach erweiterbar (z. B. COPY, BACKUP, PGM=SAVE, etc.)

---

## 📁 Projektstruktur

```plaintext
zvse/
├── index.html               # Terminal-Oberfläche
├── zvse.js                  # Terminal-Logik
├── zvse_storage.php         # API-Router (zentral)
├── api/
│   ├── datasets.php         # API für Dataset/Members
│   ├── jcl.php              # API für JCL-Ausführung
│   └── calc.php             # API für einfache Rechenoperationen
├── classes/
│   ├── DatasetManager.php   # Verwaltung von Datasets & Members
│   ├── JCLInterpreter.php   # JCL-Interpreter
│   └── Calculator.php       # Mathematische Hilfsklasse
├── zvse_storage.db          # SQLite-Datenbank (wird automatisch angelegt)
```

---

## 🚀 Installation

1. **Klonen** des Repositories:

   ```bash
   git clone https://github.com/marcdziersan/z-VSE-Websystem-Terminal-Simulation-Dataset-Management.git zvse
   cd zvse
   ```

2. **PHP-Server starten** (z. B. mit PHP CLI):

   ```bash
   php -S localhost:8000
   ```

3. **Im Browser öffnen:**

   ```
   http://localhost:8000
   ```

---

## 🖥️ Terminal-Befehle

### System

```text
HELP                  → Diese Hilfe anzeigen
DATE                  → Aktuelles Datum anzeigen
CLEAR                 → Terminal leeren
```

### Dataset-Verwaltung

```text
CREATE DSN=SYS1.LIBRARY
LISTCAT ALL
RENAME DSN=ALT NEU=NEUENAME
DELETE DSN=NAME
```

### Member-Verwaltung

```text
ADD MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO CONTENT=Hallo Welt!
READ MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO
DELETE MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO
```

### Rechnen im Terminal

```text
CALC ADD 4 5      → 9
CALC SUB 10 3     → 7
CALC MUL 3 7      → 21
CALC DIV 8 2      → 4
```

### JCL-Ausführung

```text
JCL RUN //JOB1 JOB (P),'DRUCKEN'\n//DD1 DD DSN=SYS1.LIBRARY\n//STEP1 EXEC PGM=PRINT

JCL RUN //JOB2 JOB (P),'RECHNEN'\n//IN1 DD DSN=A.NUM1\n//IN2 DD DSN=A.NUM2\n//STEP1 EXEC PGM=ADD
```

---

## 🔧 Unterstützte JCL-Kommandos

| PGM        | Beschreibung                                 |
|------------|----------------------------------------------|
| `LISTCAT`  | Listet alle Datasets                         |
| `PRINT`    | Gibt alle Inhalte der referenzierten Datasets aus |
| `ADD`      | Addiert Werte aus zwei numerischen Datasets  |

> ⚙️ Erweiterungen wie `SUB`, `DELETE`, `COPY`, `SAVE` sind vorbereitet und einfach hinzufügbar.

---

## 👨‍💻 Mitwirken / Weiterentwickeln

Das Projekt ist vollständig modular und ideal für Erweiterungen:

- Neue Kommandos in `runCommand()` (JS)
- Neue `PGM=...`-Programme in `JCLInterpreter.php`
- Neue APIs via `api/*.php`
- Neue Terminal-Funktionen im JS

Pull Requests und Feedback willkommen!

---

## 📘 Lizenz

Dieses Projekt steht unter der MIT-Lizenz. Du darfst es frei nutzen, ändern und verbreiten.

---

**Viel Spaß mit z/VSE – Web Edition!**
