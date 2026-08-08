# Briefing — Image Studio (Blog CriaSys Web): Grupo + Degradê

**Para:** agente que mantém o Image Studio no Blog CriaSys Web (produto completo).  
**Origem:** MarkCraft (studio gratuito / isca) — **já implementado** no MarkCraft; **espelhar** no Blog.  
**Data:** 31/07/2026  
**Não alterar** este briefing no MarkCraft além de manter o espelho alinhado.

---

## O que falta no Image Studio do Blog

Duas features que o usuário pediu e o MarkCraft passou a ter:

1. **Agrupador de objetos** — várias camadas viram um **grupo** permanente (e dá para **desagrupar**).
2. **Criador de degradês** no quadro **Cor & contorno** — preenchimento **sólido**, **linear** e **radial** (não só `input type="color"`).

---

## 1) Agrupar / Desagrupar

### Comportamento esperado
- Usuário seleciona **2+** objetos (Shift+clique, marquee, ou **Ctrl/Shift+clique na lista de camadas**).
- Botão **Agrupar** (e atalho **Ctrl+G**) → um `Group` Fabric permanente no **mesmo lugar** da seleção.
- **Separar** / **Ctrl+Shift+G** → filhos voltam + seleção múltipla.
- Contador “N selecionadas” na lista de camadas.

### Bugs conhecidos (já corrigidos no MarkCraft — espelhar o fix)
1. **`@click` no painel** destrói a `ActiveSelection` antes do handler → 3 objetos viram 2. **Usar `@mousedown.prevent.stop`** e um **snapshot** (`_imageStudioGroupBag`) da seleção.
2. **Grupo fora da prancheta** — criar `Group` sem sair do plano da ActiveSelection. Fluxo: capturar AABB → `removeAll`/sair do plano canvas → `new Group` → recentrar no centro da AABB → `clamp` na prancheta.

### Stack
- Fabric **6/7** (sem `toGroup` / `toActiveSelection` do v5).
- Importar: `Group`, `ActiveSelection` de `fabric`.

### Engine (referência MarkCraft)
Arquivo: `resources/js/image-studio/imageStudio.js`

Métodos a espelhar:
- `resolveObjectsForGrouping` / `groupObjects` / `ungroupActiveObject`
- `ensureObjectsOnCanvasPlane` / `getObjectsSceneBounds` / `clampObjectCenterToArtboard`
- `setMultiSelection` (Ctrl+clique nas camadas)
- `canGroupActiveSelection` / `canUngroupActiveObject`

### UI
- Botões **Agrupar** / **Separar** com `@mousedown.prevent.stop` (igual Excluir).
- Barra “N selecionadas · Agrupar” acima da lista.
- Atalhos Ctrl+G / Ctrl+Shift+G.

---

## 2) Degradê linear + radial no quadro de cores

### Comportamento esperado
Painel **Cor & contorno** (formas, não linha):
- Modos: **Sólida** | **Linear** | **Radial**
- Sólida: color picker + “Sem preenchimento” (como hoje).
- Linear: Cor A, Cor B, slider **ângulo** 0–360°.
- Radial: Cor A, Cor B (centro → borda).
- Ao re-selecionar a forma, UI lê o fill atual (sólido ou `Gradient`).

### Engine
Já existe `Gradient` do Fabric e `resolveTemplateFill` (só linear em templates). Reaproveitar ideia.

Helpers MarkCraft a espelhar:
- `buildShapeGradientFill({ mode, colorA, colorB, angle })`
- `readShapeFillState(fill)`
- `getShapeStyleFromObject` → inclui `fillMode`, `gradientColorA/B`, `gradientAngle`
- `applyShapePaint` → se `fillMode` linear/radial, `object.set('fill', new Gradient(...))`

Coords sugeridas (`gradientUnits: 'percentage'`):
- Linear: a partir do ângulo (0° = esquerda→direita).
- Radial: `{ x1:0.5, y1:0.5, r1:0, x2:0.5, y2:0.5, r2:0.65 }`.

### UI Alpine
Estado:
- `imageStudioFillMode`: `'solid' | 'linear' | 'radial'`
- `imageStudioGradientColorA` / `imageStudioGradientColorB`
- `imageStudioGradientAngle`

Métodos: `imageStudioSetFillMode`, `imageStudioOnGradientChange` (reusam `applyShapePaint`).

View: `image_studio_sidebar_panels` (ou equivalente no Blog) — seção Cor & contorno.

### Escopo MVP
- Só **preenchimento de formas** (rect, circle, path, etc.).
- Contorno continua sólido.
- Texto / fundo da prancheta: **fora** deste MVP (pode ser fase 2).

---

## Arquivos MarkCraft (fonte da verdade desta entrega)

| Peça | Path |
|------|------|
| Engine + Alpine methods | `resources/js/image-studio/imageStudio.js` |
| UI sidebar | `resources/views/studio/partials/image_studio_sidebar_panels.blade.php` |
| Entry | `resources/js/image-studio/app-studio.js` |

Desktop MarkCraft consome o **mesmo** Studio JS — não precisa feature Electron à parte.

---

## Critérios de aceite (Blog)

- [ ] Shift+selecionar 2 formas → Grupo → 1 camada; mover grupo move tudo.
- [ ] Separar restaura camadas individuais selecionáveis.
- [ ] Forma com fill linear/radial exporta PNG/JPG com o degradê visível.
- [ ] Reabrir arte salva (se o Blog persiste JSON) mantém grupo e degradê.
- [ ] Undo/redo não quebra grupo nem fill Gradient.

---

## Copy / produto

MarkCraft = isca gratuita. Blog CriaSys Web = joia.  
Layouts ≠ Pacotes. Evitar copy “isca/joia/cozinha” na UI do studio do usuário final se o Blog já tiver tom próprio — foque em **Agrupar** / **Degradê**.
