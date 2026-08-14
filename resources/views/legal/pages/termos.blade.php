@php
    $product = $legal['product_name'] ?? 'MarkCraft';
    $brand = $legal['brand_family'] ?? 'CriaSys';
    $controller = $legal['controller_name'] ?? 'CriaSys';
    $email = $legal['contact_email'] ?? 'contato@criasysweb.com.br';
@endphp

<p>
    Estes Termos de Uso (“Termos”) regem o acesso e a utilização do <strong>{{ $product }}</strong>,
    studio de imagem gratuito da família <strong>{{ $brand }}</strong>, operado por
    <strong>{{ $controller }}</strong> (“nós”, “nosso”).
</p>
<p>
    Ao acessar o site, criar conta ou usar o Studio, você concorda com estes Termos e com a
    <a href="{{ route('legal.show', 'privacidade') }}">Política de Privacidade</a>,
    a <a href="{{ route('legal.show', 'cookies') }}">Política de Cookies</a> e a
    <a href="{{ route('legal.show', 'uso-aceitavel') }}">Política de Uso Aceitável</a>.
    Se não concordar, interrompa o uso imediatamente.
</p>

<h2>1. O serviço</h2>
<p>
    O {{ $product }} oferece ferramentas online para criação e edição de imagens (layouts, elementos, exportações
    e recursos correlatos), tipicamente no navegador. O produto é disponibilizado <strong>gratuitamente</strong>,
    sem garantia de disponibilidade contínua, e pode evoluir, mudar ou ser descontinuado a qualquer momento.
</p>
<p>
    Recursos avançados opcionais (doações, afiliados quando configurados) são ofertas relacionadas da família
    {{ $brand }}, com condições próprias quando aplicáveis.
</p>

<h2>2. Elegibilidade e conta</h2>
<ul>
    <li>Você declara ter capacidade civil para aceitar estes Termos ou estar devidamente representado.</li>
    <li>Informações de cadastro devem ser verdadeiras e atualizadas.</li>
    <li>Você é responsável por manter a confidencialidade da senha e por atividades realizadas na conta.</li>
    <li>Podemos suspender ou encerrar contas que violem estes Termos, a Política de Uso Aceitável ou a lei.</li>
</ul>

<h2>3. Licença de uso da plataforma</h2>
<p>
    Concedemos a você uma licença limitada, pessoal, não exclusiva, intransferível e revogável para usar o
    {{ $product }} conforme estes Termos. Não é permitido:
</p>
<ul>
    <li>copiar, modificar, distribuir ou criar obras derivadas do software/interface além do permitido por lei;</li>
    <li>fazer engenharia reversa, scraping abusivo ou exploração de vulnerabilidades;</li>
    <li>revender, sublicenciar ou apresentar o {{ $product }} como se fosse seu produto próprio;</li>
    <li>usar automações que degradem o serviço ou burlem limites técnicos (rate limits, autenticação etc.).</li>
</ul>

<h2>4. Seu conteúdo</h2>
<p>
    Você mantém os direitos sobre imagens, textos e arquivos que carregar ou criar (“Conteúdo do Usuário”),
    ressalvados direitos de terceiros (fontes, fotos, marcas, templates licenciados).
</p>
<ul>
    <li>Você declara ter autorização para usar o Conteúdo do Usuário e assume responsabilidade exclusiva por ele.</li>
    <li>Ao usar o serviço, você nos concede licença limitada para processar o conteúdo apenas na medida necessária
        à prestação do {{ $product }} (sessão, exportação, ferramentas auxiliares). A remoção de fundo padrão roda no seu dispositivo.</li>
    <li>Não reivindicamos propriedade das artes que você exportar do Studio.</li>
    <li>Conteúdo ilegal ou que viole a Política de Uso Aceitável pode ser removido e a conta sancionada.</li>
</ul>

<h2>5. Propriedade intelectual nossa</h2>
<p>
    Marcas, layout, identidade visual e textos de marketing do {{ $product }} e da família
    {{ $brand }} são protegidos por lei. O código-fonte do MarkCraft é disponibilizado sob a
    GNU Affero General Public License v3 (AGPL-3.0) em
    <a href="https://github.com/pvitorv/MarkCraft" target="_blank" rel="noopener">github.com/pvitorv/MarkCraft</a>.
    Bibliotecas de terceiros (Laravel, Fabric.js, @imgly/background-removal, etc.) permanecem nas respectivas licenças.
</p>

<h2>6. Serviços de terceiros</h2>
<p>
    O site pode integrar ou linkar serviços de terceiros (analytics, anúncios, pagamentos, packs afiliados,
    blog, biblioteca de remoção de fundo no navegador). Não controlamos esses serviços e não nos
    responsabilizamos por suas políticas, disponibilidade ou práticas. O uso deles está sujeito aos termos dos terceiros.
</p>

<h2>7. Doações, afiliados e avisos comerciais</h2>
<ul>
    <li><strong>Doações</strong> são voluntárias e não criam obrigação de entrega de funcionalidade específica.</li>
    <li><strong>Links de afiliados</strong> podem gerar comissão à {{ $brand }} se você comprar em sites parceiros;
        isso não aumenta o preço por si só, mas é uma relação comercial transparente.</li>
    <li>Materiais promocionais no site são informativos; ofertas de terceiros podem mudar sem aviso prévio nosso.</li>
</ul>

<h2>8. Disponibilidade e alterações</h2>
<p>
    Podemos modificar, suspender ou encerrar funcionalidades, inclusive o Studio desktop/web, para manutenção,
    segurança ou evolução do produto. Faremos esforços razoáveis para comunicar mudanças relevantes, sem obrigação
    de aviso prévio em casos urgentes (segurança, abuso, força maior).
</p>

<h2>9. Isenção de garantias</h2>
<p>
    O {{ $product }} é fornecido <strong>“como está”</strong> e <strong>“conforme disponível”</strong>, sem garantias
    expressas ou implícitas de adequação a um propósito específico, ausência de erros, continuidade, precisão de
    exportações, compatibilidade com todos os dispositivos ou resultados comerciais.
</p>

<h2>10. Limitação de responsabilidade</h2>
<p>
    Na máxima extensão permitida pela lei brasileira aplicável a relações de consumo e civis:
</p>
<ul>
    <li>não respondemos por lucros cessantes, perda de dados no dispositivo do usuário, danos indiretos ou
        consequenciais decorrentes do uso ou da impossibilidade de uso do {{ $product }};</li>
    <li>não respondemos por conteúdo gerado ou carregado por usuários, nem por violações de direitos de terceiros
        praticadas por usuários;</li>
    <li>nossa responsabilidade total, se reconhecida judicialmente, limita-se, quando cabível, ao montante
        efetivamente pago por você a nós nos 12 (doze) meses anteriores ao evento (em regra R$&nbsp;0,00 no plano gratuito),
        ressalvados direitos indisponíveis do consumidor e hipóteses de dolo ou culpa grave.</li>
</ul>

<h2>11. Indenização</h2>
<p>
    Você concorda em indenizar e isentar {{ $controller }} / {{ $brand }} de reclamações de terceiros decorrentes
    do seu Conteúdo do Usuário, do uso indevido do serviço ou da violação destes Termos, na medida da sua responsabilidade.
</p>

<h2>12. Rescisão</h2>
<p>
    Você pode deixar de usar o serviço e solicitar exclusão de conta a qualquer momento.
    Podemos encerrar ou restringir o acesso em caso de violação, risco de segurança, exigência legal ou descontinuação do produto.
</p>

<h2>13. Lei aplicável e foro</h2>
<p>
    Estes Termos são interpretados conforme as leis do {{ $legal['jurisdiction'] ?? 'Brasil' }}.
    Fica eleito o foro da comarca do domicílio do controlador, ou o foro do consumidor quando a lei assim exigir,
    para dirimir controvérsias, com renúncia a qualquer outro, por mais privilegiado que seja, na medida permitida.
</p>

<h2>14. Disposições gerais</h2>
<ul>
    <li>Se alguma cláusula for inválida, as demais permanecem em vigor.</li>
    <li>A tolerância a infrações não constitui renúncia de direitos.</li>
    <li>Estes Termos, junto às políticas linkadas, constituem o acordo integral sobre o uso do {{ $product }}.</li>
</ul>

<h2>15. Contato</h2>
<p>
    Dúvidas sobre estes Termos: <a href="mailto:{{ $email }}">{{ $email }}</a>.
</p>
