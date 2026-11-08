<?php

namespace App\Support;

class ErrorPage
{
    public static function content(int $status): array
    {
        [$title, $description, $action, $href] = match ($status) {
            401 => ['Vamos entrar novamente?', 'Entre na sua conta para continuar acompanhando o desafio.', 'Entrar na minha conta', '/login'],
            403 => ['Este acesso não está disponível', 'Sua conta não tem permissão para acessar esta página. Volte ao início ou fale com o suporte se precisar de ajuda.', 'Voltar ao início', '/'],
            404 => ['Esse caminho não foi encontrado', 'A página pode ter mudado de endereço ou o link pode estar incompleto. Seu próximo passo no desafio continua esperando por você.', 'Voltar ao início', '/'],
            419 => ['Sua sessão expirou', 'Esta página ficou aberta por muito tempo ou sua sessão foi renovada. Entre novamente e refaça a última ação. As alterações que não foram enviadas podem precisar ser preenchidas outra vez.', 'Entrar novamente', '/login'],
            429 => ['Vamos dar uma pequena pausa', 'Recebemos muitas tentativas em pouco tempo. Aguarde um minuto antes de tentar novamente.', 'Voltar ao início', '/'],
            503 => ['Estamos preparando tudo para você', 'O desafio está temporariamente indisponível. Aguarde alguns instantes e tente acessar novamente.', 'Tentar acessar novamente', '/'],
            default => ['Encontramos um imprevisto', 'Não foi possível concluir esta ação agora. Tente acessar novamente em alguns instantes. Se continuar acontecendo, fale com o suporte.', 'Voltar ao início', '/'],
        };

        return compact('status', 'title', 'description', 'action', 'href');
    }
}
