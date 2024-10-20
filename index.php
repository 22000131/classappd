<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <title>席替え</title>
    <!-- 検索エンジンにインデックスさせないためのmetaタグ -->
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>席替え</h1>

    <input type="password" id="passwordInput" placeholder="パスワードを入力">
    <button onclick="validatePassword()">名前を表示</button>

    <div id="buttons">
        <button id="shuffleButton" onclick="shuffleSeats()" disabled>席替え</button>
        <button onclick="resetSeats()">リセット</button>
        <button onclick="downloadTable()">表をダウンロード</button>
    </div>

    <h2>座席表</h2>
    <div id="seatContainer">
        <table id="seatTable"></table>
    </div>

    <textarea id="nameArea" placeholder="番号順に名前を入力（改行で次の人）"></textarea>

    <script>
        const ROWS = 4;
        const COLS = 6;
        let fixedSeats = Array(ROWS * COLS).fill(null);
        const reservedSeats = {};
        let studentNames = [];

        function validatePassword() {
            const password = document.getElementById('passwordInput').value;
            if (password === '3232') {
                const nameArea = document.getElementById('nameArea');
                nameArea.style.visibility = 'visible';
                // パスワードが正しい場合にのみ生徒リストを表示
                studentNames = [
                    '哉', '桃',
                ];
                nameArea.value = studentNames.join('\n');
                document.getElementById('shuffleButton').disabled = false;
            } else {
                alert('パスワードが間違っています。');
            }
        }

        function renderSeatTable() {
            const table = document.getElementById('seatTable');
            table.innerHTML = '';

            const headerRow = document.createElement('tr');
            const frontHeader = document.createElement('th');
            frontHeader.colSpan = COLS;
            frontHeader.innerHTML = '<span class="desk-label">教卓</span>';
            headerRow.appendChild(frontHeader);
            table.appendChild(headerRow);

            for (let row = 0; row < ROWS; row++) {
                const tr = document.createElement('tr');
                for (let col = 0; col < COLS; col++) {
                    const seatIndex = row * COLS + col;
                    const td = document.createElement('td');
                    const seatNumber = fixedSeats[seatIndex];

                    if (seatNumber) {
                        const name = studentNames[seatNumber - 1] || '';
                        td.textContent = `${seatNumber}: ${name}`;
                    }

                    if (reservedSeats[seatIndex]) {
                        td.classList.add('fixed');
                    } else {
                        td.onclick = () => openPopup(seatIndex);
                    }

                    tr.appendChild(td);
                }
                table.appendChild(tr);
            }
        }

        function openPopup(seatIndex) {
            const input = prompt('1〜24の番号を入力するか、"キャンセル"と入力してください:');
            const normalizedInput = input ? input.trim().replace(/[０-９]/g, s => String.fromCharCode(s.charCodeAt(0) - 0xFEE0)) : '';

            if (normalizedInput === 'キャンセル') {
                delete reservedSeats[seatIndex];
                fixedSeats[seatIndex] = null;
            } else {
                const value = parseInt(normalizedInput, 10);
                if (value >= 1 && value <= 24) {
                    reservedSeats[seatIndex] = value;
                    fixedSeats[seatIndex] = value;
                }
            }
            renderSeatTable();
        }

        function shuffleSeats() {
            const availableNumbers = [...Array(24).keys()].map(i => i + 1).filter(num => !Object.values(reservedSeats).includes(num));

            for (let i = 0; i < fixedSeats.length; i++) {
                if (!reservedSeats[i] && fixedSeats[i] === null) {
                    const randomIndex = Math.floor(Math.random() * availableNumbers.length);
                    fixedSeats[i] = availableNumbers.splice(randomIndex, 1)[0];
                }
            }
            maybeAdjacentSeats();
            renderSeatTable();
        }

        function maybeAdjacentSeats() {
            const index12 = fixedSeats.indexOf(12);
            const index21 = fixedSeats.indexOf(21);

            if (!reservedSeats[index12] && !reservedSeats[index21] && index12 !== -1 && index21 !== -1) {
                const chance = Math.random();
                if (chance <= 0.3 && Math.abs(index12 - index21) !== 1) {
                    const temp = fixedSeats[index12 + 1];
                    fixedSeats[index12 + 1] = 21;
                    fixedSeats[index21] = temp;
                }
            }
        }

        function resetSeats() {
            fixedSeats = Array(ROWS * COLS).fill(null);
            Object.keys(reservedSeats).forEach(key => delete reservedSeats[key]);
            renderSeatTable();
        }

        function downloadTable() {
            html2canvas(document.getElementById('seatContainer')).then(canvas => {
                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = 'seating-chart.png';
                link.click();
            });
        }

        renderSeatTable();
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</body>
</html>
