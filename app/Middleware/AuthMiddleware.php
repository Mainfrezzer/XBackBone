<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\ResponseFactory;

class AuthMiddleware extends Middleware
{
    /**
     * @param Request        $request
     * @param RequestHandler $handler
     *
     * @return ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        if (!$this->session->get('logged', false)) {
            $this->session->set('redirectTo', (string) $request->getUri()->getPath());

            return redirect((new ResponseFactory())->createResponse(), route('login.show'));
        }

        if (!$this->database->query('SELECT `id`, `active` FROM `users` WHERE `id` = ? LIMIT 1', [$this->session->get('user_id')])->fetch()->active) {
            $this->session->alert(lang('account_disabled'), 'danger');
            $this->session->set('logged', false);

            return redirect((new ResponseFactory())->createResponse(), route('login.show'));
        }

        return $handler->handle($request);
    }
}
