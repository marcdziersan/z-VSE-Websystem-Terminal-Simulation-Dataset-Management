// Referenz auf Terminal-Ausgabebereich und Eingabefeld
const terminal = document.getElementById('terminal');
const input = document.getElementById('input');

// Lokale Kommandos, die direkt im Frontend verarbeitet werden
const localCommands = {
  // Hilfe anzeigen
  HELP: `Verfügbare Befehle:

System:
  HELP                         - Diese Hilfe anzeigen
  DATE                         - Aktuelles Datum anzeigen
  CLEAR                        - Terminal leeren

Dataset-Verwaltung:
  CREATE DSN=name              - Neues Dataset erstellen
    Beispiel: CREATE DSN=SYS1.LIBRARY

  LISTCAT ALL                  - Alle vorhandenen Datasets anzeigen

Member-Verwaltung:
  ADD MEMBER DSN=dataset MEMBER=member CONTENT=text
                               - Neuen Member zum Dataset hinzufügen
    Beispiel: ADD MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO CONTENT=Hallo Welt!

  READ MEMBER DSN=dataset MEMBER=member
                               - Member lesen
    Beispiel: READ MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO

  DELETE MEMBER DSN=dataset MEMBER=member
                               - Member löschen
    Beispiel: DELETE MEMBER DSN=SYS1.LIBRARY MEMBER=HELLO

Rechnen (Terminal):
  CALC ADD a b                - Addieren zweier Zahlen
    Beispiel: CALC ADD 4 5
  CALC SUB a b                - Subtraktion
    Beispiel: CALC SUB 10 3
  CALC MUL a b                - Multiplikation
  CALC DIV a b                - Division

JCL-Ausführung (mit \\n für Zeilenumbruch):
  JCL RUN jcl-code            - Führt JCL-Batchjob aus
    Beispiel:
    JCL RUN //JOB1 JOB (P),'TEST'\\n//DD1 DD DSN=SYS1.LIBRARY\\n//STEP1 EXEC PGM=PRINT
`,

  // Aktuelles Datum anzeigen
  DATE: () => `Aktuelles Datum: ${new Date().toLocaleString()}`,

  // Terminal-Ausgabe leeren
  CLEAR: () => {
    terminal.textContent = '';
    return null;
  }
};

// Gibt Text im Terminal aus und scrollt automatisch nach unten
function print(text) {
  if (text !== null) terminal.textContent += `\n${text}`;
  terminal.scrollTop = terminal.scrollHeight;
}

// Hauptfunktion zur Verarbeitung eines eingegebenen Befehls
function runCommand(cmd) {
  const trimmed = cmd.trim();
  const upper = trimmed.toUpperCase();

  // Lokale Frontend-Befehle wie HELP, DATE, CLEAR
  if (localCommands[upper]) {
    const response = typeof localCommands[upper] === 'function'
      ? localCommands[upper]()
      : localCommands[upper];
    print(response);
    return;
  }

  // LISTCAT ALL → zeigt alle Datasets an
  if (upper === 'LISTCAT ALL') {
    fetch('zvse_storage.php?action=list_all')
      .then(res => res.text())
      .then(print);
    return;
  }

  // CREATE DSN=... → neues Dataset anlegen
  if (trimmed.startsWith('CREATE DSN=')) {
    const name = trimmed.split('=')[1];
    fetch('zvse_storage.php?action=create_dataset', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `name=${encodeURIComponent(name)}&type=PDS`
    }).then(res => res.text()).then(print);
    return;
  }

  // ADD MEMBER ... → Member zum Dataset hinzufügen
  if (trimmed.startsWith('ADD MEMBER')) {
    const match = /DSN=(\S+) MEMBER=(\S+) CONTENT=(.+)/i.exec(trimmed);
    if (match) {
      const [_, dataset, member, content] = match;
      fetch('zvse_storage.php?action=add_member', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `dataset=${encodeURIComponent(dataset)}&member=${encodeURIComponent(member)}&content=${encodeURIComponent(content)}`
      }).then(res => res.text()).then(print);
    } else {
      print('Syntaxfehler bei ADD MEMBER');
    }
    return;
  }

  // READ MEMBER ... → Inhalt eines Members anzeigen
  if (trimmed.startsWith('READ MEMBER')) {
    const match = /DSN=(\S+) MEMBER=(\S+)/i.exec(trimmed);
    if (match) {
      const [_, dataset, member] = match;
      fetch(`zvse_storage.php?action=read_member&dataset=${encodeURIComponent(dataset)}&member=${encodeURIComponent(member)}`)
        .then(res => res.text()).then(print);
    } else {
      print('Syntaxfehler bei READ MEMBER');
    }
    return;
  }

  // DELETE MEMBER ... → Member löschen
  if (trimmed.startsWith('DELETE MEMBER')) {
    const match = /DSN=(\S+) MEMBER=(\S+)/i.exec(trimmed);
    if (match) {
      const [_, dataset, member] = match;
      fetch(`zvse_storage.php?action=delete_member&dataset=${encodeURIComponent(dataset)}&member=${encodeURIComponent(member)}`)
        .then(res => res.text()).then(print);
    } else {
      print('Syntaxfehler bei DELETE MEMBER');
    }
    return;
  }

  // RENAME DSN=ALT NEU=NEUENAME → Dataset umbenennen
  if (trimmed.startsWith('RENAME DSN=')) {
    const match = /RENAME DSN=(\S+) NEU=(\S+)/i.exec(trimmed);
    if (match) {
      const [_, oldName, newName] = match;
      fetch('zvse_storage.php?action=rename_dataset', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `old=${encodeURIComponent(oldName)}&new=${encodeURIComponent(newName)}`
      }).then(res => res.text()).then(print);
    } else {
      print('Syntaxfehler bei RENAME DSN');
    }
    return;
  }

  // DELETE DSN=NAME → Dataset löschen
  if (trimmed.startsWith('DELETE DSN=')) {
    const match = /DELETE DSN=(\S+)/i.exec(trimmed);
    if (match) {
      const name = match[1];
      fetch(`zvse_storage.php?action=delete_dataset&name=${encodeURIComponent(name)}`)
        .then(res => res.text()).then(print);
    } else {
      print('Syntaxfehler bei DELETE DSN');
    }
    return;
  }

  // JCL RUN ... → JCL-Job interpretieren (z. B. PRINT, ADD)
  if (trimmed.startsWith('JCL RUN')) {
    const jcl = trimmed.substring(8).replaceAll("\\n", "\n");
    fetch('zvse_storage.php?action=run_jcl', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `jcl=${encodeURIComponent(jcl)}`
    }).then(res => res.text()).then(print);
    return;
  }

  // CALC OP A B → einfache Arithmetik (Add, Sub, Mul, Div)
  if (trimmed.startsWith('CALC')) {
    const parts = trimmed.split(' ');
    const op = parts[1]?.toUpperCase();
    const a = parseFloat(parts[2]);
    const b = parseFloat(parts[3]);
    let result;

    if (isNaN(a) || isNaN(b)) {
      result = "Ungültige Zahlen.";
    } else {
      switch (op) {
        case 'ADD': result = a + b; break;
        case 'SUB': result = a - b; break;
        case 'MUL': result = a * b; break;
        case 'DIV': result = b !== 0 ? a / b : 'Division durch 0!'; break;
        default: result = `Unbekannte Operation: ${op}`; break;
      }
    }

    print(`Ergebnis: ${result}`);
    return;
  }

  // Wenn kein bekannter Befehl erkannt wurde
  print(`Unbekannter Befehl: ${cmd}`);
}

// Event-Listener: Führt Befehl aus, wenn ENTER gedrückt wird
input.addEventListener('keydown', function (e) {
  if (e.key === 'Enter') {
    const cmd = input.value;
    terminal.textContent += `\n> ${cmd}`;
    runCommand(cmd);  // Befehl ausführen
    input.value = ''; // Eingabe zurücksetzen
  }
});
