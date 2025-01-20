<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indonesian Scrabble Solver</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            text-align: center;
        }
        input[type="text"], select {
            width: 80%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            padding: 10px 20px;
            border: none;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .results {
            margin-top: 20px;
            text-align: left;
        }
        .results h3 {
            margin-bottom: 10px;
        }
        .autocomplete-box {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 20px;
        }
        .autocomplete-box input {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 60px;
            text-align: center;
        }
        .autocomplete-box input.empty {
            border: 1px dashed #ccc;
        }
        .autocomplete-results {
            margin-top: 20px;
            display: none;
        }
        .autocomplete-results div {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Indonesian Scrabble Solver</h1>
        <select id="wordlist">
            <option value="wordlist.lst">Default Word List</option>
            <option value="wordlist-alternate.lst">Alternate Word List</option>
            <option value="wordlist-kbbi.lst">Word List From KBBI</option>
        </select>
        <input type="text" id="letters" placeholder="Enter letters separated by spaces">
        <button onclick="findWords()">Solve</button>
        <div class="results" id="results"></div>

        <div class="autocomplete" id="autocomplete" style="display: none;">
            <input type="number" id="wordLength" placeholder="Enter word length" min="2" max="8">
            <div class="autocomplete-box" id="autocompleteBox"></div>
            <button onclick="autocomplete()">Find Words</button>
            <div class="autocomplete-results" id="autocompleteResults"></div>
        </div>
    </div>

    <script>
        let previousResults = [];

        async function findWords() {
            const letters = document.getElementById('letters').value;
            const wordlist = document.getElementById('wordlist').value;

            const response = await fetch('/solve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ letters: letters, wordlist: wordlist })
            });

            const results = await response.json();
            previousResults = results;
            displayResults(results);

            // Show autocomplete section
            document.getElementById('autocomplete').style.display = 'block';
            clearAutocomplete();
        }

        function displayResults(results) {
            const resultsDiv = document.getElementById('results');
            resultsDiv.innerHTML = '';

            for (const length in results) {
                const wordList = results[length].join(', ');
                const section = document.createElement('div');
                section.innerHTML = `<h3>${length}-letter words</h3><p>${wordList}</p>`;
                resultsDiv.appendChild(section);
            }

            if (Object.keys(results).length === 0) {
                resultsDiv.innerHTML = '<p>No valid words found. Try different letters!</p>';
            }
        }

        async function autocomplete() {
            const length = document.getElementById('wordLength').value;
            const wordlist = document.getElementById('wordlist').value;
            const wordParts = Array.from(document.querySelectorAll('#autocompleteBox input')).map(input => input.value);

            const response = await fetch('/autocomplete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ wordlist: wordlist, length: length, wordParts: wordParts })
            });

            const results = await response.json();
            displayAutocompleteResults(results);
        }

        function displayAutocompleteResults(results) {
            const resultsDiv = document.getElementById('autocompleteResults');
            resultsDiv.innerHTML = '';

            results.forEach(word => {
                const wordDiv = document.createElement('div');
                wordDiv.textContent = word;
                resultsDiv.appendChild(wordDiv);
            });

            resultsDiv.style.display = 'block';
        }

        function clearAutocomplete() {
            document.getElementById('wordLength').value = '';
            const boxDiv = document.getElementById('autocompleteBox');
            boxDiv.innerHTML = '';
            document.getElementById('autocompleteResults').style.display = 'none';
        }

        document.getElementById('wordLength').addEventListener('input', function() {
            const length = this.value;
            const boxDiv = document.getElementById('autocompleteBox');
            boxDiv.innerHTML = '';

            for (let i = 0; i < length; i++) {
                const input = document.createElement('input');
                input.className = 'empty';
                input.placeholder = 'Letter';
                boxDiv.appendChild(input);
            }
        });
    </script>
</body>
</html>
