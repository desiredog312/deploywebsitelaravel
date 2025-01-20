<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;

class ScrabbleSolverController extends Controller
{
    protected static $validWords = [];

    protected $dictionary = [];

    public function __construct()
    {
        $this->loadDictionary('wordlist.lst'); // Default word list
    }

    protected function loadDictionary($fileName)
    {
        $path = storage_path("app/{$fileName}");
        $this->dictionary = File::exists($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
        $this->dictionary = array_map('trim', $this->dictionary);
    }

    public function solve(Request $request)
    {
        $letters = str_replace(' ', '', strtolower($request->input('letters')));
        $wordList = $request->input('wordlist', 'wordlist.lst');
        $this->loadDictionary($wordList);

        $validWords = $this->findWords(str_split($letters));
        
        // Store valid words in session
        Session::put('validWords', $validWords);

        return response()->json($validWords);
    }

    protected function findWords($letters)
    {
        $categorizedWords = [];

        foreach ($this->dictionary as $word) {
            if ($this->isValidWord($word, $letters)) {
                $length = strlen($word);
                if (!isset($categorizedWords[$length])) {
                    $categorizedWords[$length] = [];
                }
                $categorizedWords[$length][] = $word;
            }
        }

        return $categorizedWords;
    }

    protected function isValidWord($word, $letters)
    {
        $letterCount = array_count_values($letters);

        foreach (str_split($word) as $char) {
            if (!isset($letterCount[$char]) || $letterCount[$char] == 0) {
                return false;
            }
            $letterCount[$char]--;
        }

        return true;
    }

    public function autocomplete(Request $request)
    {
        // Retrieve valid words from session
        $validWords = Session::get('validWords', []);

        $letters = $request->input('letters', '');
        $length = $request->input('length', 0);
        $wordParts = $request->input('wordParts', []);

        $results = [];

        foreach ($validWords as $wordLength => $words) {
            if ($wordLength == $length) {
                foreach ($words as $word) {
                    if ($this->matchesPattern($word, $wordParts)) {
                        $results[] = $word;
                    }
                }
            }
        }

        return response()->json($results);
    }

    protected function matchesPattern($word, $pattern)
    {
        for ($i = 0; $i < strlen($word); $i++) {
            if (!empty($pattern[$i]) && $pattern[$i] != $word[$i]) {
                return false;
            }
        }
        return true;
    }
}
