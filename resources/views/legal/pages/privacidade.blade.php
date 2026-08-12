@php
    $product = $legal['product_name'] ?? 'MarkCraft';
    $brand = $legal['brand_family'] ?? 'CriaSys';
    $controller = $legal['controller_name'] ?? 'CriaSys';
    $email = $legal['contact_email'] ?? 'contato@criasysweb.com.br';
@endphp

<p>
    Esta Política de Privacidade descreve como o <strong>{{ $product }}</strong>, studio de imagem
    da família <strong>{{ $brand }}</strong>, trata dados pessoais de visitantes e usuários.
    O tratamento observa a {{ $legal['governing_law'] ?? 'LGPD' }}.
</p>
<p>
    Ao utilizar o site ou criar uma conta, você declara ter lido este documento.
    Se não concordar, não utilize o serviço.
</p>

<h2>1. Controlador e contato</h2>
<p>
    O controlador dos dados pessoais tratados no {{ $product }} é <strong>{{ $controller }}</strong>
    (família {{ $brand }}), responsável pelo produto publicado em
    <a href="{{ url('/') }}">{{ parse_url(url('/'), PHP_URL_HOST) ?: 'markcraft.criasysweb.com.br' }}</a>.
</p>
<p>
    Para exercer direitos de titular, dúvidas ou solicitações relacionadas a privacidade, escreva para
    <a href="mailto:{{ $email }}">{{ $email }}</a>.
    Responderemos no prazo razoável previsto na LGPD, observadas eventuais exigências legais adicionais.
</p>

<h2>2. Quais dados coletamos</h2>
<h3>2.1 Dados que você nos fornece</h3>
<ul>
    <li><strong>Conta:</strong> nome, e-mail e senha (armazenada de forma criptografada/hashed).</li>
    <li><strong>Comunicação:</strong> mensagens enviadas por e-mail ou formulários de suporte, quando existirem.</li>
    <li><strong>Doações / apoio:</strong> se você usar links de pagamento ou Pix indicados no site, os dados financeiros
        são processados pelo provedor de pagamento — não armazenamos número completo de cartão no {{ $product }}.</li>
</ul>

<h3>2.2 Dados gerados pelo uso</h3>
<ul>
    <li><strong>Sessão e autenticação:</strong> cookies e tokens necessários para manter login e proteger o painel.</li>
    <li><strong>Logs técnicos:</strong> endereço IP, data/hora, user-agent, páginas acessadas e códigos de erro,
        para segurança, diagnóstico e prevenção de abuso.</li>
    <li><strong>Uso do Studio:</strong> o processamento de imagens ocorre predominantemente no seu navegador.
        Uploads temporários (ex.: remoção de fundo em servidor) podem gerar arquivos transitórios, apagados após o processamento
        ou conforme política de limpeza do ambiente.</li>
    <li><strong>Ferramentas auxiliares:</strong> se você usar encurtador de links ou recursos similares, podemos guardar
        o URL original, o código curto e metadados de criação associados à conta (quando autenticado).</li>
</ul>

<h3>2.3 Dados de terceiros / métricas</h3>
<p>
    Se ativarmos ferramentas de análise ou publicidade (por exemplo Google Analytics, Tag Manager, Microsoft Clarity,
    Meta Pixel, Plausible ou AdSense), esses fornecedores podem coletar identificadores de dispositivo, cookies e
    eventos de navegação conforme as respectivas políticas. A ativação e os IDs são configuráveis no painel CMS do site.
</p>

<h2>3. Para que usamos os dados (finalidades)</h2>
<ul>
    <li>Prestar o serviço de studio, conta e páginas públicas;</li>
    <li>Autenticar usuários, manter sessão e recuperar acesso (e-mail de redefinição de senha);</li>
    <li>Garantir segurança, estabilidade e combate a fraude ou abuso;</li>
    <li>Melhorar o produto com métricas agregadas de uso (quando habilitadas);</li>
    <li>Exibir conteúdos promocionais ou anúncios, quando configurados;</li>
    <li>Cumprir obrigações legais e responder a autoridades competentes;</li>
    <li>Comunicar alterações relevantes nestas políticas ou no serviço.</li>
</ul>

<h2>4. Bases legais (LGPD)</h2>
<p>Conforme o caso, o tratamento pode se apoiar em:</p>
<ul>
    <li><strong>Execução de contrato</strong> ou procedimentos preliminares (criação e uso da conta);</li>
    <li><strong>Legítimo interesse</strong> (segurança, melhoria do produto, métricas agregadas, prevenção a abuso),
        com equilíbrio diante dos direitos do titular;</li>
    <li><strong>Consentimento</strong>, quando exigido (por exemplo, cookies não essenciais, se adotarmos banner de consentimento);</li>
    <li><strong>Cumprimento de obrigação legal</strong> ou regulatória;</li>
    <li><strong>Exercício regular de direitos</strong> em processo judicial, administrativo ou arbitral.</li>
</ul>

<h2>5. Compartilhamento</h2>
<p>Podemos compartilhar dados com:</p>
<ul>
    <li><strong>Provedores de infraestrutura</strong> (hospedagem, e-mail transacional, CDN), sob contratos e deveres de confidencialidade;</li>
    <li><strong>Ferramentas de analytics/ads</strong>, quando habilitadas no CMS;</li>
    <li><strong>Gateways de pagamento</strong>, se você optar por doar/apoiar via link externo;</li>
    <li><strong>Autoridades</strong>, quando houver obrigação legal ou ordem válida.</li>
</ul>
<p>
    Não vendemos seus dados pessoais. Links de afiliados (Packs CriaSys e similares) podem levar a sites de terceiros
    com políticas próprias — leia-as antes de comprar.
</p>

<h2>6. Transferências internacionais</h2>
<p>
    Alguns fornecedores (Google, Microsoft, Meta, provedores de nuvem etc.) podem processar dados fora do Brasil.
    Nesses casos, adotamos medidas compatíveis com a LGPD, incluindo cláusulas contratuais e escolha de fornecedores
    que declarem salvaguardas adequadas, na medida do razoavelmente possível para um produto gratuito.
</p>

<h2>7. Retenção</h2>
<ul>
    <li><strong>Conta:</strong> enquanto ativa e pelo tempo necessário após exclusão para backup, segurança ou obrigação legal.</li>
    <li><strong>Logs:</strong> tipicamente por períodos curtos a médios, salvo necessidade de investigação.</li>
    <li><strong>Arquivos temporários do Studio:</strong> o mínimo necessário ao processamento.</li>
</ul>
<p>
    Você pode solicitar exclusão da conta pela área de perfil (quando disponível) ou pelo e-mail de contato acima.
</p>

<h2>8. Seus direitos</h2>
<p>Nos termos da LGPD, você pode solicitar:</p>
<ul>
    <li>confirmação de tratamento e acesso aos dados;</li>
    <li>correção de dados incompletos, inexatos ou desatualizados;</li>
    <li>anonimização, bloqueio ou eliminação de dados desnecessários ou tratados em desconformidade;</li>
    <li>portabilidade, quando aplicável;</li>
    <li>informação sobre compartilhamentos;</li>
    <li>revogação de consentimento, quando essa for a base legal;</li>
    <li>oposição a tratamentos baseados em legítimo interesse, observados os limites legais.</li>
</ul>
<p>
    Também é possível apresentar reclamação à Autoridade Nacional de Proteção de Dados (ANPD).
</p>

<h2>9. Segurança</h2>
<p>
    Adotamos medidas técnicas e organizacionais razoáveis (HTTPS, hashing de senhas, controle de acesso administrativo,
    rate limiting em APIs). Nenhum sistema é 100% seguro; use senhas fortes e não compartilhe credenciais.
</p>

<h2>10. Crianças e adolescentes</h2>
<p>
    O {{ $product }} não é direcionado a menores de 13 anos. Se identificarmos cadastro irregular de criança,
    poderemos remover a conta. Responsáveis legais devem supervisionar o uso por adolescentes.
</p>

<h2>11. Relação com outros produtos {{ $brand }}</h2>
<p>
    O Blog CriaSys Web e outros sites da família podem ter políticas próprias. Esta política cobre o
    {{ $product }} e as páginas sob o domínio deste produto.
</p>

<h2>12. Alterações</h2>
<p>
    Podemos atualizar este texto para refletir mudanças no produto ou na lei. A data de “Última atualização”
    no topo da página indica a versão vigente. Alterações relevantes poderão ser comunicadas no site ou por e-mail.
</p>

<h2>13. Contato</h2>
<p>
    Privacidade e proteção de dados: <a href="mailto:{{ $email }}">{{ $email }}</a>.
</p>
<p class="text-xs !text-zinc-600">
    Documento informativo e contratual do serviço. Não substitui consultoria jurídica personalizada;
    recomenda-se revisão periódica por profissional de confiança do controlador.
</p>
