<?php

namespace App\Classes;

use App\Models\Config;

class AiCode
{
    public function generate($request)
    {
        // Query
        $config = Config::get();

        // Parameters
        $open_ai_key = $config[35]->config_value;
        $prompt = $request->description;

        // Parameters
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => array(["role" => "user", "content" => $prompt]),
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.openai.com/v1/chat/completions",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
                'Authorization: Bearer ' . $open_ai_key,
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            // Result
            $result = json_decode($response);

            // Check error
            if (isset($result->error)) {
                $this->result = null;
            } else {
                $this->result = $result->choices[0]->message->content;
            }
        }

        return $this->result;
    }
}
