<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UnSlay-shell</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/xterm@5.3.0/css/xterm.css" />
    <script src="https://cdn.jsdelivr.net/npm/xterm@5.3.0/lib/xterm.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/xterm-addon-fit@0.8.0/lib/xterm-addon-fit.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #000;
            height: 100vh;
            overflow: hidden;
            font-family: 'Menlo', 'Monaco', 'Courier New', monospace;
        }

        #terminal {
            width: 100%;
            height: 100%;
        }

        /* Login Modal Styles */
        #login-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .login-box {
            background-color: #111;
            border: 1px solid #333;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
            width: 300px;
        }

        .login-title {
            color: #0f0;
            margin-bottom: 1.5rem;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .login-input {
            width: 100%;
            padding: 10px;
            margin-bottom: 1rem;
            background-color: #000;
            border: 1px solid #333;
            color: #fff;
            font-family: inherit;
            box-sizing: border-box;
        }

        .login-input:focus {
            outline: none;
            border-color: #0f0;
            border-color: #0f0;
        }

        .login-btn {
            background-color: #0f0;
            color: #000;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            font-family: inherit;
            font-weight: bold;
            width: 100%;
            transition: background-color 0.2s;
        }

        .login-btn:hover {
            background-color: #0c0;
        }

        .error-msg {
            color: #f00;
            margin-top: 10px;
            font-size: 0.9rem;
            display: none;
        }
    </style>
</head>

<body>
    <div id="login-overlay">
        <div class="login-box">
            <div class="login-title">UnSlay-shell Access</div>
            <form id="login-form">
                <input type="password" id="password" class="login-input" placeholder="Enter Password" required
                    autofocus>
                <button type="submit" class="login-btn">ACCESS</button>
                <div id="error-msg" class="error-msg">Access Denied</div>
            </form>
        </div>
    </div>

    <div id="terminal"></div>

    <script>
        const loginOverlay = document.getElementById('login-overlay');
        const loginForm = document.getElementById('login-form');
        const errorMsg = document.getElementById('error-msg');
        const passwordInput = document.getElementById('password');

        @if(session('terminal_authenticated'))
            loginOverlay.style.display = 'none';
            initTerminal();
        @endif

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const password = passwordInput.value;

            try {
                const response = await fetch('{{ route("unslay-shell.login") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ password })
                });

                const data = await response.json();

                if (data.success) {
                    loginOverlay.style.display = 'none';
                    initTerminal();
                } else {
                    errorMsg.style.display = 'block';
                    passwordInput.value = '';
                    passwordInput.focus();
                }
            } catch (err) {
                console.error(err);
                errorMsg.textContent = 'Connection Error';
                errorMsg.style.display = 'block';
            }
        });

        function initTerminal() {
            const term = new Terminal({
                cursorBlink: true,
                theme: {
                    background: '#000000',
                    foreground: '#ffffff',
                    cursor: '#ffffff'
                },
                fontFamily: 'Menlo, Monaco, "Courier New", monospace',
                fontSize: 14,
                allowTransparency: true
            });

            const fitAddon = new FitAddon.FitAddon();
            term.loadAddon(fitAddon);
            term.open(document.getElementById('terminal'));
            fitAddon.fit();

            window.addEventListener('resize', () => fitAddon.fit());

            let currentLine = '';
            let cursorPos = 0;
            let commandHistory = [];
            let historyIndex = 0;
            let cwd = '{{ str_replace("\\", "\\\\", base_path()) }}';

            function prompt(reset = true) {
                term.write('\r\n\x1b[1;32m' + cwd + '\x1b[0m $ ');
                if (reset) {
                    currentLine = '';
                    cursorPos = 0;
                }
            }

            // Boot Sequence
            async function boot() {
                term.write('\x1b[2J\x1b[0;0H'); // Clear screen

                // Define Header Lines (Custom Style for "UnSlay-shell")
                const headerRaw = [
                    "",
                    "\x1b[1;36m _    _        _____ _                        _          _ _ \x1b[0m",
                    "\x1b[1;36m | |  | |      / ____| |                      | |        | | |\x1b[0m",
                    "\x1b[1;36m | |  | |_ __ | (___ | | __ _ _   _ ______ ___| |__   ___| | |\x1b[0m",
                    "\x1b[1;36m | |  | | '_ \\ \\\\___ \\| |/ _` | | | |______/ __| '_ \\ / _ \\ | |\x1b[0m",
                    "\x1b[1;36m | |__| | | | |____) | | (_| | |_| |      \\__ \\ | | |  __/ | |\x1b[0m",
                    "\x1b[1;36m  \\____/|_| |_|_____/|_|\\__,_|\\__, |      |___/_| |_|\\___|_|_|\x1b[0m",
                    "\x1b[1;36m                               __/ |                          \x1b[0m",
                    "\x1b[1;36m                              |___/                           \x1b[0m",
                    "",
                    "\x1b[1;31m\"kollaam pakshe tholpikanavilla\"\x1b[0m",
                    "\x1b[1;37mDeveloped by: \x1b[1;33mSreyas-A-S\x1b[0m",
                    "\x1b[1;37mGithub: \x1b[1;34mhttps://github.com/Sreyas-A-S/\x1b[0m",
                    ""
                ];

                // Helper to strip ANSI codes for length calculation
                const stripAnsi = (str) => str.replace(/[\u001b\u009b][[()#;?]*(?:[0-9]{1,4}(?:;[0-9]{0,4})*)?[0-9A-ORZcf-nqry=><]/g, '');

                // Print Centered
                for (let i = 0; i < headerRaw.length; i++) {
                    let line = headerRaw[i];
                    let visibleLength = stripAnsi(line).length;
                    let totalCols = term.cols;
                    let padding = Math.max(0, Math.floor((totalCols - visibleLength) / 2));

                    let output = `\x1b[${padding}G` + line + '\r\n';

                    term.write(output);
                    await new Promise(r => setTimeout(r, 20));
                }

                term.writeln('');
                term.writeln('\x1b[90mSystem Ready.\x1b[0m');
                prompt();
            }

            boot();

            // Intercept Tab key
            term.attachCustomKeyEventHandler(e => {
                if (e.key === 'Tab') {
                    // Prevent default tab behavior (changing focus)
                    e.preventDefault();
                    if (e.type === 'keydown') {
                        handleAutocomplete();
                    }
                    return false;
                }
                return true;
            });

            async function handleAutocomplete() {
                try {
                    const response = await fetch('{{ route("unslay-shell.autocomplete") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ command: currentLine, cwd })
                    });
                    const data = await response.json();

                    if (data.matches && data.matches.length > 0) {
                        if (data.matches.length === 1) {
                            const match = data.matches[0];
                            const parts = currentLine.split(' ');
                            parts.pop();
                            parts.push(match);
                            const newLine = parts.join(' ');

                            currentLine = newLine;
                            cursorPos = currentLine.length;
                            redrawLine();
                        } else {
                            term.write('\r\n');
                            term.write(data.matches.join('  '));

                            prompt(false);
                            term.write(highlight(currentLine));
                        }
                    }
                } catch (err) {
                    console.error(err);
                }
            }

            function highlight(cmd) {
                if (!cmd) return '';

                const parts = cmd.split(' ');
                let colored = '';

                for (let i = 0; i < parts.length; i++) {
                    let part = parts[i];
                    if (i === 0) {
                        colored += '\x1b[1;33m' + part + '\x1b[0m';
                    } else if (part === 'artisan') {
                        colored += '\x1b[1;35m' + part + '\x1b[0m';
                    } else if (part.startsWith('-')) {
                        colored += '\x1b[90m' + part + '\x1b[0m';
                    } else if (part.startsWith('"') || part.startsWith("'")) {
                        colored += '\x1b[1;32m' + part + '\x1b[0m';
                    } else {
                        colored += '\x1b[1;37m' + part + '\x1b[0m';
                    }

                    if (i < parts.length - 1) {
                        colored += ' ';
                    }
                }
                return colored;
            }

            function redrawLine() {
                term.write('\r\x1b[K'); // Carriage return + Clear line
                term.write('\x1b[1;32m' + cwd + '\x1b[0m $ ');
                term.write(highlight(currentLine));
                const diff = currentLine.length - cursorPos;
                if (diff > 0) {
                    term.write('\x1b[' + diff + 'D');
                }
            }

            term.onData(e => {
                switch (e) {
                    case '\r': // Enter
                        term.write('\r\n');
                        if (currentLine.trim().length > 0) {
                            if (commandHistory.length === 0 || commandHistory[commandHistory.length - 1] !== currentLine) {
                                commandHistory.push(currentLine);
                            }
                            historyIndex = commandHistory.length;
                            executeCommand(currentLine);
                        } else {
                            prompt();
                        }
                        break;
                    case '\u007F': // Backspace
                        if (cursorPos > 0) {
                            let left = currentLine.slice(0, cursorPos - 1);
                            let right = currentLine.slice(cursorPos);
                            currentLine = left + right;
                            cursorPos--;
                            redrawLine();
                        }
                        break;
                    case '\x1b[A': // Up Arrow
                        if (historyIndex > 0) {
                            historyIndex--;
                            currentLine = commandHistory[historyIndex];
                            cursorPos = currentLine.length;
                            redrawLine();
                        }
                        break;
                    case '\x1b[B': // Down Arrow
                        if (historyIndex < commandHistory.length) {
                            historyIndex++;
                            if (historyIndex < commandHistory.length) {
                                currentLine = commandHistory[historyIndex];
                                cursorPos = currentLine.length;
                            } else {
                                currentLine = '';
                                cursorPos = 0;
                            }
                            redrawLine();
                        }
                        break;
                    case '\x1b[C': // Right Arrow
                        if (cursorPos < currentLine.length) {
                            cursorPos++;
                            term.write('\x1b[C');
                        }
                        break;
                    case '\x1b[D': // Left Arrow
                        if (cursorPos > 0) {
                            cursorPos--;
                            term.write('\x1b[D');
                        }
                        break;
                    default:
                        if (e >= String.fromCharCode(0x20) && e <= String.fromCharCode(0x7E) || e >= '\u00a0') {
                            if (cursorPos === currentLine.length) {
                                currentLine += e;
                                cursorPos++;
                            } else {
                                let left = currentLine.slice(0, cursorPos);
                                let right = currentLine.slice(cursorPos);
                                currentLine = left + e + right;
                                cursorPos++;
                            }
                            redrawLine();
                        }
                }
            });

            async function executeCommand(command) {
                try {
                    const response = await fetch('{{ route("unslay-shell.execute") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ command, cwd })
                    });

                    // Handle streaming response
                    const reader = response.body.getReader();
                    const decoder = new TextDecoder();
                    let buffer = '';

                    const processData = (line) => {
                        if (!line.trim()) return;
                        try {
                            const data = JSON.parse(line);

                            if (data.clear) {
                                term.clear();
                            }

                            if (data.output) {
                                let output = data.output.replace(/\n/g, '\r\n');
                                term.write(output);
                            }

                            if (data.cwd) {
                                cwd = data.cwd;
                            }

                            if (data.logout) {
                                window.location.reload();
                            }
                        } catch (e) {
                            console.error('Error parsing JSON chunk', e);
                        }
                    };

                    while (true) {
                        const { done, value } = await reader.read();
                        if (done) break;

                        buffer += decoder.decode(value, { stream: true });
                        const lines = buffer.split('\n');

                        // Process all complete lines
                        buffer = lines.pop(); // Keep the last incomplete line in buffer

                        for (const line of lines) {
                            processData(line);
                        }
                    }

                    // Process any remaining buffer
                    if (buffer) {
                        processData(buffer);
                    }

                } catch (error) {
                    term.write('\r\nError executing command: ' + error.message);
                }
                prompt();
            }
        }
    </script>
</body>

</html>