<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use App\Models\AiMessage;
use App\Services\AI\CrmAiAssistant;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AiChatController extends Controller
{
    public function conversations(Request $request)
    {
        return AiConversation::where('user_id', $request->user()->id)
            ->latest()->limit(50)->get();
    }

    public function messages(Request $request, AiConversation $conversation)
    {
        abort_unless($conversation->user_id === $request->user()->id, 403);
        return $conversation->messages()->orderBy('id')->get();
    }

    /**
     * Streaming endpoint (SSE). Falls back to JSON when ?no_stream=1 is sent.
     */
    public function stream(Request $request, CrmAiAssistant $assistant): StreamedResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'conversation_id' => ['nullable', 'exists:ai_conversations,id'],
            'message'         => ['required', 'string', 'max:4000'],
        ]);

        $conversation = $data['conversation_id']
            ? AiConversation::where('user_id', $request->user()->id)->findOrFail($data['conversation_id'])
            : AiConversation::create([
                'user_id' => $request->user()->id,
                'title'   => mb_substr($data['message'], 0, 60),
            ]);

        AiMessage::create([
            'ai_conversation_id' => $conversation->id,
            'role'               => 'user',
            'content'            => $data['message'],
        ]);

        $structured = $assistant->structuredResponse($request->user(), $data['message']);

        if ($request->boolean('no_stream')) {
            if ($structured) {
                AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'               => 'assistant',
                    'content'            => $structured['reply'],
                    'meta'               => ['payload' => $structured['payload']],
                ]);
                return response()->json([
                    'conversation_id' => $conversation->id,
                    'reply' => $structured['reply'],
                    'payload' => $structured['payload'],
                ]);
            }

            $reply = $assistant->ask($request->user(), $conversation, $data['message']);
            AiMessage::create([
                'ai_conversation_id' => $conversation->id,
                'role'               => 'assistant',
                'content'            => $reply,
            ]);
            return response()->json(['conversation_id' => $conversation->id, 'reply' => $reply]);
        }

        return response()->stream(function () use ($assistant, $request, $conversation, $data, $structured) {
            $buffer = '';
            try {
                if ($structured) {
                    foreach (str_split($structured['reply'], 32) as $chunk) {
                        $buffer .= $chunk;
                        echo "data: " . json_encode(['delta' => $chunk]) . "\n\n";
                        @ob_flush(); @flush();
                    }
                    AiMessage::create([
                        'ai_conversation_id' => $conversation->id,
                        'role'               => 'assistant',
                        'content'            => $buffer,
                        'meta'               => ['payload' => $structured['payload']],
                    ]);
                    echo "data: " . json_encode([
                        'done' => true,
                        'conversation_id' => $conversation->id,
                        'payload' => $structured['payload'],
                    ]) . "\n\n";
                    return;
                }

                foreach ($assistant->stream($request->user(), $conversation, $data['message']) as $chunk) {
                    $buffer .= $chunk;
                    echo "data: " . json_encode(['delta' => $chunk]) . "\n\n";
                    @ob_flush(); @flush();
                }
                AiMessage::create([
                    'ai_conversation_id' => $conversation->id,
                    'role'               => 'assistant',
                    'content'            => $buffer,
                ]);
                echo "data: " . json_encode(['done' => true, 'conversation_id' => $conversation->id]) . "\n\n";
            } catch (\Throwable $e) {
                echo "data: " . json_encode(['error' => $e->getMessage()]) . "\n\n";
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
