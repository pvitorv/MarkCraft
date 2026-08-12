@php
    $product = $legal['product_name'] ?? 'MarkCraft';
    $email = $legal['contact_email'] ?? 'contato@criasysweb.com.br';
@endphp

<p>
    Esta Política de Cookies explica o uso de cookies e tecnologias semelhantes no
    <strong>{{ $product }}</strong>. Complementa a
    <a href="{{ route('legal.show', 'privacidade') }}">Política de Privacidade</a>.
</p>

<h2>1. O que são cookies</h2>
<p>
    Cookies são pequenos arquivos armazenados no seu dispositivo. Tecnologias similares incluem
    armazenamento local (localStorage), sessionStorage e pixels de rastreamento.
    Usamos esses recursos para manter sessão, segurança e, quando configurado, medir audiência.
</p>

<h2>2. Tipos que podemos utilizar</h2>

<h3>2.1 Essenciais (necessários)</h3>
<ul>
    <li>Cookie de sessão Laravel / autenticação;</li>
    <li>token CSRF para proteção de formulários;</li>
    <li>preferências mínimas de interface, quando existirem.</li>
</ul>
<p>
    Sem esses cookies, login, Studio e áreas autenticadas podem não funcionar corretamente.
    Em geral, não dependem de consentimento marketing porque são necessários à prestação do serviço.
</p>

<h3>2.2 Analytics e desempenho (opcionais)</h3>
<p>
    Quando ativados no painel CMS (Métricas), scripts de terceiros podem definir cookies ou IDs, por exemplo:
</p>
<ul>
    <li>Google Analytics 4 / Google Tag Manager;</li>
    <li>Microsoft Clarity (mapas de calor e sessões);</li>
    <li>Plausible ou equivalentes (frequentemente com pouca ou nenhuma dependência de cookies de rastreamento).</li>
</ul>
<p>
    Essas ferramentas ajudam a entender páginas visitadas, erros e qualidade da experiência —
    de forma agregada sempre que possível.
</p>

<h3>2.3 Publicidade e marketing (opcionais)</h3>
<ul>
    <li>Meta Pixel (Facebook/Instagram), se configurado;</li>
    <li>Google AdSense ou tags de anúncio no Studio, se ativados.</li>
</ul>
<p>
    Podem ser usados para medir campanhas ou exibir anúncios. Sujeitos às políticas dos respectivos fornecedores.
</p>

<h3>2.4 Preferências locais do Studio</h3>
<p>
    O editor pode guardar rascunhos, histórico recente ou preferências de UI no armazenamento do navegador
    (localStorage). Esses dados ficam no seu dispositivo e não substituem backup em nuvem.
</p>

<h2>3. Base legal e controle</h2>
<p>
    Cookies essenciais sustentam a execução do serviço. Cookies de analytics/ads, quando usados,
    apoiam-se em legítimo interesse e/ou consentimento, conforme a implementação vigente e a LGPD.
</p>
<p>Você pode:</p>
<ul>
    <li>bloquear ou apagar cookies nas configurações do navegador;</li>
    <li>usar modo anônimo (com limitações de login);</li>
    <li>instalar extensões de privacidade;</li>
    <li>gerenciar preferências de anúncios nas plataformas Google/Meta, quando aplicável.</li>
</ul>
<p>
    Bloquear cookies essenciais pode impedir o uso da conta e do Studio.
</p>

<h2>4. Retenção</h2>
<p>
    A duração varia: cookies de sessão expiram ao fechar o navegador; cookies persistentes seguem o prazo
    definido pelo emissor (nós ou o terceiro). Consulte também as políticas dos fornecedores listados acima.
</p>

<h2>5. Atualizações</h2>
<p>
    Se adicionarmos novas ferramentas de métricas ou ads pelo CMS, esta política permanece válida em suas
    categorias; a lista concreta de IDs ativos está sob controle administrativo do site.
    Atualizaremos a data no topo quando houver mudança material neste texto.
</p>

<h2>6. Contato</h2>
<p>
    Dúvidas sobre cookies: <a href="mailto:{{ $email }}">{{ $email }}</a>.
</p>
