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
            // Prepare the email data
            $emailData = [
                'from' => $email->getFrom()[0]->getAddress(),
                'to' => $email->getTo()[0]->getAddress(),
                'email' => $email->getFrom()[0]->getAddress(),  // Assuming the sender's email
                'subject' => $email->getSubject(),
                'message' => $email->getHtmlBody(),
            ];

            // Attach files if there are any
            $attachments = $email->getAttachments();
            foreach ($attachments as $attachment) {
                $emailData['documents'][] = [
                    'name' => $attachment->getFilename(),
                    'contents' => $attachment->getBody(),
                    'content_type' => $attachment->getContentType(),
                ];
            }

            // Enviar la solicitud con archivos adjuntos
            $response = Http::withToken($jwt)->post($host, $emailData);

            // Enviar la solicitud con archivos adjuntos
            //$response = Http::withToken($jwt)->attach($emailData)->post($host);

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
