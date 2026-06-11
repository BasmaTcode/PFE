<?php
// ================================================================
// services/AiService.php — Central AI API Wrapper
// Rise & Shine Beauty AI Platform
// Supports: Google Gemini 1.5 Flash | OpenAI GPT-4o-mini
// ================================================================

class AiService {

    // ─── Core LLM Chat ───────────────────────────────────────────
    public static function chat(array $messages, array $options = []): ?string {
        $provider = defined('AI_PROVIDER') ? AI_PROVIDER : 'gemini';
        return ($provider === 'openai')
            ? self::openAiChat($messages, $options)
            : self::geminiChat($messages, $options);
    }

    // ─── Google Gemini 1.5 Flash ──────────────────────────────────
    private static function geminiChat(array $messages, array $options): ?string {
        $apiKey = AI_API_KEY;
        $model  = $options['model'] ?? AI_MODEL;
        $url    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        // Build system instruction + contents
        $systemText = '';
        $contents   = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                $systemText = $msg['content'];
                continue;
            }
            $role = ($msg['role'] === 'assistant') ? 'model' : 'user';
            $contents[] = [
                'role'  => $role,
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // Prepend system text to first user message if any
        if ($systemText && !empty($contents)) {
            $contents[0]['parts'][0]['text'] = $systemText . "\n\n" . $contents[0]['parts'][0]['text'];
        }

        $payload = [
            'contents'         => $contents,
            'generationConfig' => [
                'maxOutputTokens' => $options['max_tokens'] ?? 2000,
                'temperature'     => $options['temperature'] ?? 0.65,
            ],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => defined('AI_TIMEOUT') ? AI_TIMEOUT : 45,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            error_log("[AiService] Gemini error HTTP {$httpCode}: {$curlErr} | {$response}");
            return null;
        }

        $data = json_decode($response, true);
        return $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
    }

    // ─── OpenAI GPT-4o-mini ───────────────────────────────────────
    private static function openAiChat(array $messages, array $options): ?string {
        $payload = [
            'model'       => $options['model'] ?? 'gpt-4o-mini',
            'messages'    => $messages,
            'temperature' => $options['temperature'] ?? 0.65,
            'max_tokens'  => $options['max_tokens'] ?? 2000,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => defined('AI_TIMEOUT') ? AI_TIMEOUT : 45,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . AI_API_KEY,
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($ch);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) { error_log("[AiService] OpenAI error: {$curlErr}"); return null; }

        $data = json_decode($response, true);
        return $data['choices'][0]['message']['content'] ?? null;
    }

    // ─── Parse JSON from LLM response (strips markdown fences) ───
    private static function parseJsonResponse(?string $text): ?array {
        if (!$text) return null;
        $json = trim($text);
        // Strip ```json ... ``` fences
        $json = preg_replace('/^```(?:json)?\s*/i', '', $json);
        $json = preg_replace('/\s*```\s*$/', '', $json);
        $result = json_decode(trim($json), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[AiService] JSON parse error: " . json_last_error_msg() . " | Raw: " . substr($json, 0, 300));
            return null;
        }
        return $result;
    }

    // ─── Diagnostic Analyzer ──────────────────────────────────────
    /**
     * Generate expert analysis, routine & advice for a skin diagnostic result.
     * Returns an array with keys: expertAnalysis, routine, usageAdvice, keyIngredients
     */
    public static function generateDiagnosticAnalysis(array $axisScores, string $skinTypeLabel): ?array {
        $h = round($axisScores['hydration']   ?? 50);
        $s = round($axisScores['sebum']       ?? 50);
        $e = round($axisScores['sensitivity'] ?? 50);
        $a = round($axisScores['aging']       ?? 50);

        $prompt = "Tu es un dermatologue cosmétique expert et conseiller beauté IA de luxe.

Profil de peau du client :
- Type de peau classifié : {$skinTypeLabel}
- Score Hydratation : {$h}/100 (100 = très hydratée, 0 = très sèche)
- Score Sébum : {$s}/100 (100 = très grasse, 0 = très peu de sébum)
- Score Sensibilité : {$e}/100 (100 = très sensible/réactive, 0 = résistante)
- Score Vieillissement : {$a}/100 (100 = signes d'âge marqués, 0 = peau juvénile)

Génère une analyse dermatologique experte COMPLÈTE et PERSONNALISÉE en français.
IMPORTANT : Réponds UNIQUEMENT avec du JSON valide, sans markdown, sans texte avant ou après.

Format JSON requis :
{
  \"expertAnalysis\": {
    \"strengths\": [\"force 1\", \"force 2\", \"force 3\"],
    \"fragilities\": [\"fragilité 1\", \"fragilité 2\"],
    \"warnings\": [\"conseil de vigilance 1\", \"conseil 2\"]
  },
  \"routine\": {
    \"morning\": [
      {\"step\": \"Nettoyage\", \"advice\": \"conseil détaillé et personnalisé\"},
      {\"step\": \"Soin ciblé\", \"advice\": \"conseil détaillé\"},
      {\"step\": \"Hydratation\", \"advice\": \"conseil détaillé\"},
      {\"step\": \"Protection solaire\", \"advice\": \"conseil détaillé\"}
    ],
    \"evening\": [
      {\"step\": \"Démaquillage\", \"advice\": \"conseil détaillé\"},
      {\"step\": \"Exfoliation\", \"advice\": \"fréquence et méthode personnalisées\"},
      {\"step\": \"Soin de nuit\", \"advice\": \"conseil détaillé\"},
      {\"step\": \"Contour des yeux\", \"advice\": \"conseil détaillé\"}
    ]
  },
  \"usageAdvice\": {
    \"frequency\": \"conseil global de fréquence d'application\",
    \"avoidCombinations\": [\"combinaison à éviter 1\", \"combinaison 2\"],
    \"tips\": [\"astuce 1\", \"astuce 2\", \"astuce 3\"]
  },
  \"keyIngredients\": {
    \"recommended\": [\"ingrédient 1\", \"ingrédient 2\", \"ingrédient 3\", \"ingrédient 4\"],
    \"avoid\": [\"ingrédient à éviter 1\", \"ingrédient 2\"]
  }
}";

        $rawText = self::chat([
            ['role' => 'system', 'content' => 'Tu es un expert dermatologue cosmétique IA de luxe. Réponds UNIQUEMENT en JSON valide sans aucun texte supplémentaire.'],
            ['role' => 'user',   'content' => $prompt],
        ], ['temperature' => 0.6, 'max_tokens' => 2500]);

        return self::parseJsonResponse($rawText);
    }

    // ─── Look Recommender ─────────────────────────────────────────
    /**
     * Generate 3 personalized makeup look recommendations based on skin profile.
     * Returns: ['looks' => [...]]
     */
    public static function generateLookRecommendations(
        array  $axisScores,
        string $skinTypeLabel,
        string $occasion = 'everyday',
        string $preferences = ''
    ): ?array {
        $h = round($axisScores['hydration']   ?? 50);
        $s = round($axisScores['sebum']       ?? 50);
        $e = round($axisScores['sensitivity'] ?? 50);
        $a = round($axisScores['aging']       ?? 50);

        $occasionFr = [
            'everyday'   => 'Quotidien / Naturel',
            'soiree'     => 'Soirée / Glamour',
            'work'       => 'Professionnel / Bureau',
            'editorial'  => 'Éditorial / Avant-garde',
            'romantic'   => 'Romantique / Date',
        ][$occasion] ?? 'Quotidien';

        $prefsSection = $preferences
            ? "\nPréférences supplémentaires du client : {$preferences}"
            : '';

        $prompt = "Tu es directeur artistique beauté et dermatologue cosmétique.

Profil cutané :
- Type de peau : {$skinTypeLabel}
- Hydratation : {$h}/100
- Sébum : {$s}/100
- Sensibilité : {$e}/100
- Vieillissement : {$a}/100
- Occasion : {$occasionFr}{$prefsSection}

Génère 3 recommandations de looks maquillage PERSONNALISÉES et CRÉATIVES en français.
Chaque look doit tenir compte du type de peau (ex: peau grasse → finish mat, peau sèche → finish lumineux).
IMPORTANT : Réponds UNIQUEMENT avec du JSON valide, sans markdown, sans texte avant ou après.

{
  \"looks\": [
    {
      \"name\": \"Nom créatif du look en français\",
      \"style\": \"NATUREL\" ou \"SOIREE\" ou \"EDITORIAL\" ou \"ROMANTIQUE\" ou \"PROFESSIONNEL\",
      \"tagline\": \"une phrase accrocheur décrivant le look\",
      \"description\": \"2-3 phrases détaillées sur ce look et pourquoi il convient à ce profil\",
      \"skin_compatibility\": \"Explication courte de la compatibilité avec ce type de peau\",
      \"color_palette\": [\"couleur 1\", \"couleur 2\", \"couleur 3\"],
      \"products_steps\": [
        {\"step\": \"Pré-Base\", \"recommendation\": \"conseil produit précis et adapté\"},
        {\"step\": \"Teint\", \"recommendation\": \"conseil produit précis\"},
        {\"step\": \"Yeux\", \"recommendation\": \"conseil produit précis\"},
        {\"step\": \"Lèvres\", \"recommendation\": \"conseil produit précis\"},
        {\"step\": \"Fixation\", \"recommendation\": \"conseil produit précis\"}
      ],
      \"key_products\": [\"type produit 1\", \"type produit 2\", \"type produit 3\"],
      \"avoid\": [\"ce qu'il faut éviter pour ce type de peau\"],
      \"tags\": [\"tag1\", \"tag2\", \"tag3\"]
    }
  ]
}";

        $rawText = self::chat([
            ['role' => 'system', 'content' => 'Tu es un expert en maquillage et directeur artistique beauté de luxe. Réponds UNIQUEMENT en JSON valide.'],
            ['role' => 'user',   'content' => $prompt],
        ], ['temperature' => 0.78, 'max_tokens' => 3000]);

        return self::parseJsonResponse($rawText);
    }

    // ─── Beauty Chat Reply ────────────────────────────────────────
    /**
     * Generate a beauty advisor chat reply.
     */
    public static function chatAdvisorReply(string $userMessage, array $history = [], ?array $skinProfile = null): ?string {
        $profileContext = '';
        if ($skinProfile) {
            $profileContext = "Le client a un profil de peau : {$skinProfile['skinTypeLabel']}. "
                . "Hydratation: {$skinProfile['hydration']}/100, "
                . "Sébum: {$skinProfile['sebum']}/100, "
                . "Sensibilité: {$skinProfile['sensitivity']}/100.";
        }

        $systemPrompt = "Tu es une conseillère beauté IA de luxe pour Rise & Shine, une plateforme de beauté premium.
Tu es chaleureuse, professionnelle, et experte en soins de peau et maquillage.
Parle toujours en français. Sois précise et personnalisée dans tes conseils.
Garde tes réponses concises (2-4 phrases max) mais utiles.
" . $profileContext;

        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Add last 6 messages of history for context
        foreach (array_slice($history, -6) as $msg) {
            if (!empty($msg['role']) && !empty($msg['content'])) {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return self::chat($messages, ['temperature' => 0.75, 'max_tokens' => 450]);
    }

    // ─── Multimodal Image + Quiz Skin Analyzer ─────────────────────────
    /**
     * Call Gemini to analyze a face photo base64 along with quiz answers to generate a unified skin analysis.
     */
    public static function analyzeSkinFromImageAndQuestions(string $base64Image, string $mimeType, array $qaList): ?array {
        $apiKey = AI_API_KEY;
        $model  = 'gemini-2.0-flash'; // use the fast multimodal model
        $url    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $systemPrompt = "Tu es un dermatologue cosmétique expert de luxe et conseiller beauté IA.
Tu dois analyser la photo de visage fournie par le client ET croiser cette analyse visuelle avec les réponses qu'il a fournies au questionnaire de diagnostic pour générer un diagnostic dermatologique unifié, complet et d'une précision optimale en français.";

        // Format the QA list into a text string
        $qaText = "";
        foreach ($qaList as $index => $qa) {
            $qaText .= ($index + 1) . ". Question : " . $qa['question'] . "\n   Réponse de l'utilisateur : " . $qa['answer'] . "\n";
        }

        $prompt = "Voici les réponses fournies par l'utilisateur au questionnaire dermatologique :\n" . $qaText . "\n" .
"Analyse maintenant la photo de visage fournie tout en combinant tes observations visuelles avec ces réponses de l'utilisateur.
Évalue précisément les 4 dimensions suivantes sur une échelle de 0 à 100 :
1. Hydratation (0 = très déshydratée, 100 = parfaitement hydratée)
2. Sébum (0 = très sèche/sensible, 100 = très grasse/excès de sébum)
3. Sensibilité (0 = résistante, 100 = hyper-réactive/sensible)
4. Signes de l'âge (0 = peau jeune/ferme, 100 = ridée/relâchée)

Détermine ensuite le type de peau prédominant parmi :
- 'dry' (Peau Sèche)
- 'oily' (Peau Grasse)
- 'sensitive' (Peau Sensible)
- 'mature' (Peau Mature)
- 'normal' (Peau Normale)

Fournis également une analyse d'expert avec ses points forts, ses fragilités, des conseils de vigilance, une routine de soins personnalisée du matin et du soir, des conseils d'utilisation (fréquence, combinaisons à éviter, astuces) et les ingrédients recommandés et à éviter.

Retourne uniquement un objet JSON correspondant exactement à ce schéma :
{
  \"skinTypeCode\": \"dry\" | \"oily\" | \"sensitive\" | \"mature\" | \"normal\",
  \"skinTypeLabel\": \"Peau Sèche\" | \"Peau Grasse\" | \"Peau Sensible\" | \"Peau Mature\" | \"Peau Normale\",
  \"scores\": {
    \"hydration\": nombre entre 0 et 100,
    \"sebum\": nombre entre 0 et 100,
    \"sensitivity\": nombre entre 0 et 100,
    \"aging\": nombre entre 0 et 100
  },
  \"expertAnalysis\": {
    \"strengths\": [\"force 1\", \"force 2\", \"force 3\"],
    \"fragilities\": [\"fragilité 1\", \"fragilité 2\"],
    \"warnings\": [\"conseil de vigilance 1\", \"conseil 2\"]
  },
  \"routine\": {
    \"morning\": [
      {\"step\": \"Nettoyage\", \"advice\": \"conseil personnalisé\"},
      {\"step\": \"Hydratation\", \"advice\": \"conseil\"},
      {\"step\": \"Protection solaire\", \"advice\": \"conseil\"}
    ],
    \"evening\": [
      {\"step\": \"Démaquillage\", \"advice\": \"conseil\"},
      {\"step\": \"Soin de nuit\", \"advice\": \"conseil\"}
    ]
  },
  \"usageAdvice\": {
    \"frequency\": \"conseil fréquence\",
    \"avoidCombinations\": [\"à éviter 1\"],
    \"tips\": [\"astuce 1\", \"astuce 2\"]
  },
  \"keyIngredients\": {
    \"recommended\": [\"ingrédient 1\", \"ingrédient 2\"],
    \"avoid\": [\"ingrédient à éviter 1\"]
  }
}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => 3000,
                'temperature' => 0.4
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            error_log("[AiService] Gemini Skin Image + Quiz Analysis error HTTP {$httpCode}: {$curlErr} | {$response}");
            return null;
        }

        $data = json_decode($response, true);
        $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return self::parseJsonResponse($rawText);
    }

    // ─── Image Skin Analyzer (Multimodal) ─────────────────────────
    /**
     * Call Gemini to analyze a face photo base64 and estimate skin scores/analysis.
     */
    public static function analyzeSkinFromImage(string $base64Image, string $mimeType): ?array {
        $apiKey = AI_API_KEY;
        $model  = 'gemini-2.0-flash'; // use the fast multimodal model
        $url    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        $systemPrompt = "Tu es un dermatologue cosmétique expert de luxe et conseiller beauté IA.
Tu dois analyser la photo de visage fournie par le client et générer un diagnostic dermatologique complet en français.";

        $prompt = "Analyse cette photo de visage pour estimer les caractéristiques dermatologiques de l'utilisateur.
Évalue précisément les 4 dimensions suivantes sur une échelle de 0 à 100 :
1. Hydratation (0 = très déshydratée, 100 = parfaitement hydratée)
2. Sébum (0 = très sèche/sensible, 100 = très grasse/excès de sébum)
3. Sensibilité (0 = résistante, 100 = hyper-réactive/sensible)
4. Signes de l'âge (0 = peau jeune/ferme, 100 = ridée/relâchée)

Détermine ensuite le type de peau prédominant parmi :
- 'dry' (Peau Sèche)
- 'oily' (Peau Grasse)
- 'sensitive' (Peau Sensible)
- 'mature' (Peau Mature)
- 'normal' (Peau Normale)

Fournis également une analyse d'expert avec ses points forts, ses fragilités, des conseils de vigilance, une routine de soins personnalisée du matin et du soir, des conseils d'utilisation (fréquence, combinaisons à éviter, astuces) et les ingrédients recommandés et à éviter.

Retourne uniquement un objet JSON correspondant exactement à ce schéma :
{
  \"skinTypeCode\": \"dry\" | \"oily\" | \"sensitive\" | \"mature\" | \"normal\",
  \"skinTypeLabel\": \"Peau Sèche\" | \"Peau Grasse\" | \"Peau Sensible\" | \"Peau Mature\" | \"Peau Normale\",
  \"scores\": {
    \"hydration\": nombre entre 0 et 100,
    \"sebum\": nombre entre 0 et 100,
    \"sensitivity\": nombre entre 0 et 100,
    \"aging\": nombre entre 0 et 100
  },
  \"expertAnalysis\": {
    \"strengths\": [\"force 1\", \"force 2\", \"force 3\"],
    \"fragilities\": [\"fragilité 1\", \"fragilité 2\"],
    \"warnings\": [\"conseil de vigilance 1\", \"conseil 2\"]
  },
  \"routine\": {
    \"morning\": [
      {\"step\": \"Nettoyage\", \"advice\": \"conseil personnalisé\"},
      {\"step\": \"Hydratation\", \"advice\": \"conseil\"},
      {\"step\": \"Protection solaire\", \"advice\": \"conseil\"}
    ],
    \"evening\": [
      {\"step\": \"Démaquillage\", \"advice\": \"conseil\"},
      {\"step\": \"Soin de nuit\", \"advice\": \"conseil\"}
    ]
  },
  \"usageAdvice\": {
    \"frequency\": \"conseil fréquence\",
    \"avoidCombinations\": [\"à éviter 1\"],
    \"tips\": [\"astuce 1\", \"astuce 2\"]
  },
  \"keyIngredients\": {
    \"recommended\": [\"ingrédient 1\", \"ingrédient 2\"],
    \"avoid\": [\"ingrédient à éviter 1\"]
  }
}";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemPrompt . "\n\n" . $prompt],
                        [
                            'inlineData' => [
                                'mimeType' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'maxOutputTokens' => 3000,
                'temperature' => 0.4
            ]
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr || $httpCode !== 200) {
            error_log("[AiService] Gemini Skin Image Analysis error HTTP {$httpCode}: {$curlErr} | {$response}");
            return null;
        }

        $data = json_decode($response, true);
        $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        return self::parseJsonResponse($rawText);
    }
}
