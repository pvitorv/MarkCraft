@php
    $product = $legal['product_name'] ?? 'MarkCraft';
    $brand = $legal['brand_family'] ?? 'CriaSys';
    $email = $legal['contact_email'] ?? 'contato@criasysweb.com.br';
@endphp

<p>
    Esta Política de Uso Aceitável (“PUA”) define condutas permitidas e proibidas no
    <strong>{{ $product }}</strong>. Integra os
    <a href="{{ route('legal.show', 'termos') }}">Termos de Uso</a> e protege usuários, terceiros e a família
    <strong>{{ $brand }}</strong>.
</p>

<h2>1. Princípios</h2>
<ul>
    <li>Use o Studio de boa-fé, para criar conteúdo legítimo.</li>
    <li>Respeite leis brasileiras e direitos de propriedade intelectual, imagem e personalidade.</li>
    <li>Não prejudique a infraestrutura, outros usuários ou a reputação do {{ $product }}.</li>
</ul>

<h2>2. Conteúdo proibido</h2>
<p>É vedado carregar, criar, exportar ou distribuir via o serviço conteúdo que:</p>
<ul>
    <li>seja ilegal, ou incentive crime, violência, terrorismo ou tráfico;</li>
    <li>contenha exploração sexual de menores (tolerância zero) ou pornografia não consensual;</li>
    <li>promova ódio, discriminação ilegal ou assédio grave;</li>
    <li>infrinja direitos autorais, marcas, desenhos industriais ou segredos comerciais de terceiros
        sem autorização;</li>
    <li>utilize imagem de pessoa sem base legal ou consentimento, quando exigido;</li>
    <li>seja malware, phishing, engenharia social ou tentativa de fraude;</li>
    <li>divulgue dados pessoais sensíveis de terceiros sem base legal;</li>
    <li>imite-se a spam massivo ou conteúdo enganoso de golpe financeiro.</li>
</ul>

<h2>3. Uso técnico proibido</h2>
<ul>
    <li>Ataques, scans agressivos, exploração de falhas ou sobrecarga deliberada (DoS);</li>
    <li>tentativa de acesso a contas ou áreas administrativas alheias;</li>
    <li>bypass de autenticação, limites de API ou proteções antiabuso;</li>
    <li>uso de bots que prejudiquem estabilidade sem autorização nossa;</li>
    <li>redistribuição do software do Studio em violação à licença aplicável a componentes de terceiros
        (ex.: bibliotecas de remoção de fundo com termos próprios).</li>
</ul>

<h2>4. Propriedade intelectual de terceiros</h2>
<p>
    Fontes, fotos de banco, logos de marcas e templates podem ter licenças restritas.
    É sua responsabilidade verificar se pode usá-los comercialmente. O {{ $product }} não audita
    automaticamente cada exportação e não concede direitos que você não possua.
</p>

<h2>5. Denúncias e remoção</h2>
<p>
    Se identificar conteúdo ou uso abusivo, envie detalhes para
    <a href="mailto:{{ $email }}">{{ $email }}</a> (URL, prints, descrição).
    Podemos remover conteúdo, suspender contas e, se necessário, cooperar com autoridades.
</p>
<p>
    O Studio aplica <strong>filtro automático</strong> (IA no navegador) que tenta bloquear nudez e pornografia
    explícitas no upload e na exportação. Lingerie, biquíni e sunga em geral são permitidos.
    O filtro <strong>não detecta com certeza</strong> crimes como exploração infantil ou zoofilia —
    esses usos são proibidos e devem ser denunciados; a barreira automática é preventiva, não absoluta.
</p>
<p>
    Notificações de infração a direito autoral devem trazer identificação do titular, descrição da obra,
    URL/localização do material e declaração de boa-fé sobre a titularidade.
</p>

<h2>6. Consequências</h2>
<p>Violações podem resultar em:</p>
<ul>
    <li>aviso, restrição de recursos ou suspensão temporária;</li>
    <li>exclusão de conta e bloqueio de novos cadastros;</li>
    <li>comunicação a autoridades ou titulares de direitos prejudicados;</li>
    <li>medidas judiciais cabíveis.</li>
</ul>

<h2>7. Relação com o Studio desktop</h2>
<p>
    A versão desktop que carrega o {{ $product }} localmente permanece sujeita a esta PUA e aos Termos,
    além de eventuais termos do instalador/Electron.
</p>

<h2>8. Atualizações</h2>
<p>
    Podemos atualizar esta PUA conforme o produto evolui. O uso continuado após a publicação da nova versão
    constitui aceite das alterações, salvo disposição legal em contrário.
</p>

<h2>9. Contato</h2>
<p>
    Uso aceitável e denúncias: <a href="mailto:{{ $email }}">{{ $email }}</a>.
</p>
