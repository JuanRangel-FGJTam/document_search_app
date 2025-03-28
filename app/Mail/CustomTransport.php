<?php

namespace App\Mail;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomTransport extends AbstractTransport
{
    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        try {
            $email = MessageConverter::toEmail($message->getOriginalMessage());

            $jwt = config('mail.mailers.dgtitAPI.jwt');
            $host = config('mail.mailers.dgtitAPI.host');

            // Create multipart form request
            $request = Http::withToken($jwt)->asMultipart();

            // Attach basic fields
            $request->attach('from', $email->getFrom()[0]->getAddress(), null, ['Content-Type' => 'text/plain']);
            $request->attach('to', $email->getTo()[0]->getAddress(), null, ['Content-Type' => 'text/plain']);
            $request->attach('subject', $email->getSubject(), null, ['Content-Type' => 'text/plain']);
            $request->attach('message', $email->getHtmlBody(), null, ['Content-Type' => 'text/html']);

            foreach ($email->getAttachments() as $attachment) {
                $content = (string) $attachment->getBody(); // ensure content is string
                $friendlyFilename = 'constancia_de_extravio.pdf';

                $request->attach(
                    'documents', // field name expected by the API
                    $content,
                    $friendlyFilename,
                    ['Content-Type' => 'application/pdf']
                );
            }

            // Send the request
            $response = $request->post($host);

            if (!$response->successful()) {
                Log::error('Failed to send email API status code: ' . $response->status());
                Log::error('Failed to send email API: ' . $response->json());
                throw new TransportException('Failed to send email: ' . $response->json());
            }
        } catch (TransportExceptionInterface $e) {
            Log::error('Failed to send email TransportExceptionInterface: ' . $e->getMessage());
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to send email Exception: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'dgtitAPI';
    }
}
