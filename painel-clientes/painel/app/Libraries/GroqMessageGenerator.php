<?php

namespace App\Libraries;

class GroqMessageGenerator
{
    private const MODEL    = 'openai/gpt-oss-120b';
    private const ENDPOINT = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct(private string $apiKey)
    {
    }

    public function generate(array $prospect, string $adminName, string $saudacao): string
    {
        if ($this->apiKey === '') {
            throw new \RuntimeException('API Key do Groq não configurada. Configure em Configurações de IA.');
        }

        $body = json_encode([
            'model'    => self::MODEL,
            'messages' => [
                ['role' => 'system', 'content' => $this->buildSystemPrompt($adminName, $saudacao)],
                ['role' => 'user',   'content' => $this->buildUserPrompt($prospect)],
            ],
        ]);

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: application/json\r\nAuthorization: Bearer {$this->apiKey}\r\n",
                'content'       => $body,
                'timeout'       => 20,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents(self::ENDPOINT, false, $context);

        if ($raw === false) {
            throw new \RuntimeException('Não foi possível conectar à API do Groq. Tente novamente.');
        }

        $data = json_decode($raw, true);

        if (isset($data['error'])) {
            throw new \RuntimeException('Groq: ' . ($data['error']['message'] ?? 'erro desconhecido'));
        }

        $text = $data['choices'][0]['message']['content'] ?? null;

        if (! $text) {
            throw new \RuntimeException('A IA não retornou uma mensagem. Tente novamente.');
        }

        return trim($text);
    }

    private function buildSystemPrompt(string $adminName, string $saudacao): string
    {
        return <<<PROMPT
Você é {$adminName}, dono(a) de uma agência que cria sites, sistemas e apps para pequenos negócios locais.

Escreva uma mensagem de WhatsApp curta (3 a 4 linhas) para o primeiro contato com o negócio que o usuário descrever, oferecendo a criação de um site (ou sistema/app, se fizer mais sentido pelo contexto). A mensagem deve:
- Começar com uma saudação natural baseada neste horário do dia: "{$saudacao}". Pode variar a forma de dizer isso (ex: "Boa tarde!", "Oi, boa tarde", etc.) — não precisa ser sempre a mesma frase, mas o período do dia deve ser esse.
- Soar natural e pessoal, como se {$adminName} tivesse pesquisado sobre o negócio antes de mandar, não genérica nem robótica.
- Mencionar algo específico sobre o negócio (nome, nota ou avaliações no Google) quando disponível, sem exagerar em elogios.
- Ter um tom leve e consultivo, sem pressão de venda ("hard sell").
- Terminar com uma pergunta simples de baixo compromisso (ex: se pode mostrar algo rápido, sem obrigação).
- Não incluir NENHUM emoji (o link de abrir o WhatsApp corrompe alguns emojis no pré-preenchimento).
- Revise mentalmente a frase antes de responder, sem repetir palavras por engano (ex: "vi vi", "que que").
- Responda APENAS com o texto da mensagem, sem aspas, sem explicações, sem markdown.
PROMPT;
    }

    private function buildUserPrompt(array $prospect): string
    {
        $name    = $prospect['name'] ?? '';
        $rating  = $prospect['rating']        ? number_format((float) $prospect['rating'], 1, ',', '') : null;
        $reviews = $prospect['reviews_count'] ? number_format((int) $prospect['reviews_count'])         : null;

        $context = "Nome do negócio: {$name}\n";
        if ($rating)  $context .= "Nota no Google: {$rating}\n";
        if ($reviews) $context .= "Número de avaliações no Google: {$reviews}\n";
        if (! empty($prospect['company']))  $context .= "Empresa/observação: {$prospect['company']}\n";
        if (! empty($prospect['interest'])) $context .= "Interesse identificado: {$prospect['interest']}\n";
        if (! empty($prospect['notes']))    $context .= "Notas internas: {$prospect['notes']}\n";

        return $context;
    }
}
