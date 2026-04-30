<?php
require_once 'config.php';

class AIAssistant {
    private $api_key;
    private $api_url;
    private $user_id;
    private $user_type;
    
    public function __construct($user_id, $user_type) {
        $this->api_key = GEMINI_API_KEY;
        $this->api_url = GEMINI_API_URL;
        $this->user_id = $user_id;
        $this->user_type = $user_type;
    }
    
    // Отправка запроса к Gemini API
    public function sendMessage($message, $context = 'general') {
        global $system_prompt;
        
        // Добавляем контекст в зависимости от типа пользователя
        $context_prompt = $this->getContextPrompt($context);
        
        $prompt = $system_prompt . "\n\n" . $context_prompt . "\n\nПайдаланушы: " . $message;
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'maxOutputTokens' => 800,
                'topP' => 0.8,
                'topK' => 40
            ]
        ];
        
        $url = $this->api_url . '?key=' . $this->api_key;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Логируем для отладки
        error_log("Gemini API Response Code: " . $http_code);
        error_log("Gemini API Response: " . substr($response, 0, 500));
        
        if ($http_code == 200) {
            $result = json_decode($response, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => false,
                    'response' => 'JSON қатесі',
                    'debug' => json_last_error_msg()
                ];
            }
            
            $ai_response = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'Кешіріңіз, жауап алу мүмкін болмады.';
            
            // Сохраняем в историю
            $this->saveToHistory($message, $ai_response, $context);
            
            return [
                'success' => true,
                'response' => $ai_response
            ];
        } else {
            // Логируем ошибку для отладки
            $error_details = json_decode($response, true);
            $error_message = 'Белгісіз қате';
            
            if ($http_code == 302) {
                $error_message = 'API key дұрыс емес немесе жарамсыз. Конфигурацияны тексеріңіз.';
            } elseif ($http_code == 400) {
                $error_message = $error_details['error']['message'] ?? 'Сұраныс форматы дұрыс емес';
            } elseif ($http_code == 403) {
                $error_message = 'API key-ге рұқсат жоқ немесе квота біткен';
            } elseif ($http_code == 404) {
                $error_message = 'API endpoint табылмады';
            } elseif ($curl_error) {
                $error_message = 'Байланыс қатесі: ' . $curl_error;
            } elseif (isset($error_details['error']['message'])) {
                $error_message = $error_details['error']['message'];
            }
            
            return [
                'success' => false,
                'response' => 'Қате орын алды. API код: ' . $http_code,
                'debug' => $error_message
            ];
        }
    }
    
    // Получение контекстного промпта
    private function getContextPrompt($context) {
        switch ($context) {
            case 'resume':
                return "Пайдаланушы резюме жазуға көмек сұрап отыр. Кеңестер бер: не жазу керек, қандай дағдыларды көрсету керек, қалай жақсы көрсету керек.";
            case 'vacancy':
                return "Пайдаланушы вакансия жазуға көмек сұрап отыр. Кеңестер бер: қандай талаптар қою керек, міндеттерді қалай сипаттау керек, жалақы туралы не жазу керек.";
            case 'cover_letter':
                return "Пайдаланушы сүйемелдеу хатын жазуға көмек сұрап отыр. Қысқа әрі тартымды хат үлгісін көрсет.";
            case 'interview':
                return "Пайдаланушы сұхбатқа дайындалуға көмек сұрап отыр. Кеңестер бер: қандай сұрақтар қоюы мүмкін, қалай жауап беру керек.";
            default:
                return "";
        }
    }
    
    // Сохранение в историю
    private function saveToHistory($user_message, $ai_response, $context) {
        require_once '../config/database.php';
        
        try {
            $stmt = $pdo->prepare("INSERT INTO ai_chat_history (user_id, user_message, ai_response, context) VALUES (?, ?, ?, ?)");
            $stmt->execute([$this->user_id, $user_message, $ai_response, $context]);
        } catch (Exception $e) {
            // Ошибка сохранения, но не прерываем выполнение
        }
    }
}
?>