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

            // Construir la solicitud con los campos del correo
            $requestData = [
                ['name' => 'from', 'contents' => $email->getFrom()[0]->getAddress()],
                ['name' => 'to', 'contents' => $email->getTo()[0]->getAddress()],
                ['name' => 'subject', 'contents' => $email->getSubject()],
                ['name' => 'message', 'contents' => $email->getHtmlBody()],
            ];

            // Adjuntar archivos si existen
            foreach ($email->getAttachments() as $attachment) {
                $requestData[] = [
                    'name'     => 'attachments[]',
                    'contents' => $attachment->getBody(),
                    'filename' => $attachment->getFilename(),
                ];
            }

            // Enviar la solicitud con archivos adjuntos
            $response = Http::withToken($jwt)->attach($requestData)->post($host);

            if (!$response->successful()) {
                Log::error('Failed to send email API status code: ' . $response->status());
                Log::error('Failed to send email API: ' . $response);
                throw new TransportException('Failed to send email: ' . $response);
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
