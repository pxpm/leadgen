const { chromium } = require('playwright');
const fs = require('fs');

const SERVICES = [
    // Already done: telhados reparação, substituição, pintura casas, pintura exterior, instalar AC

    // Telhados
    { name: 'Telhados-Limpeza', url: 'https://www.fixando.pt/servicos/limpeza-de-telhado' },
    
    // Canalização (try multiple URLs)
    { name: 'Canalizador', url: 'https://www.fixando.pt/servicos/canalizador' },
    { name: 'Canalizacao-Reparacao', url: 'https://www.fixando.pt/servicos/reparacao-de-tubos-de-canalizacao' },
    { name: 'Canalizacao-Instalacao', url: 'https://www.fixando.pt/servicos/instalacao-de-tubos-de-canalizacao' },
    { name: 'Desentupimentos', url: 'https://www.fixando.pt/servicos/desentupimentos' },
    
    // Electricidade
    { name: 'Eletricista', url: 'https://www.fixando.pt/servicos/eletricista' },
    { name: 'Eletricidade-Problemas', url: 'https://www.fixando.pt/servicos/problemas-eletricos-e-de-cabos' },
    { name: 'Eletricidade-Instalacao', url: 'https://www.fixando.pt/servicos/instalacao-de-interruptores-e-tomadas' },
    
    // AC/Climatização
    { name: 'AC-Reparacao', url: 'https://www.fixando.pt/servicos/reparacao-de-ar-condicionado' },
    { name: 'AC-Manutencao', url: 'https://www.fixando.pt/servicos/manutencao-de-ar-condicionado' },
    { name: 'Aquecimento', url: 'https://www.fixando.pt/servicos/instalacao-ou-substituicao-de-sistemas-de-aquecimento' },
    
    // Jardinagem
    { name: 'Jardinagem', url: 'https://www.fixando.pt/servicos/jardinagem' },
    { name: 'Limpeza-Terrenos', url: 'https://www.fixando.pt/servicos/limpeza-de-terrenos' },
    { name: 'Corte-Relva', url: 'https://www.fixando.pt/servicos/corte-e-aparacao-de-relvado' },
    { name: 'Rega', url: 'https://www.fixando.pt/servicos/instalacao-de-sistema-de-rega-gota-a-gota' },
    
    // Pragas
    { name: 'Desinfestacao', url: 'https://www.fixando.pt/servicos/controlo-de-pragas' },
    { name: 'Desbaratizacao', url: 'https://www.fixando.pt/servicos/desbaratizacao' },
    
    // Construção/Remodelação
    { name: 'Remodelacao-Cozinhas', url: 'https://www.fixando.pt/servicos/remodelacao-de-cozinhas' },
    { name: 'Remodelacao-WC', url: 'https://www.fixando.pt/servicos/remodelacao-de-casa-de-banho' },
    { name: 'Construcao-Civil', url: 'https://www.fixando.pt/servicos/construcao-civil' },
    { name: 'Pladur', url: 'https://www.fixando.pt/servicos/instalacao-de-paredes-de-pladur' },
    { name: 'Pavimento-Flutuante', url: 'https://www.fixando.pt/servicos/instalacao-de-pavimento-flutuante' },
    
    // Impermeabilização
    { name: 'Impermeabilizacao', url: 'https://www.fixando.pt/servicos/impermeabilizacao-da-casa' },
    { name: 'Isolamento', url: 'https://www.fixando.pt/servicos/isolamentos' },
    { name: 'Capoto', url: 'https://www.fixando.pt/servicos/capoto' },
    
    // Painéis Solares
    { name: 'Paineis-Solares', url: 'https://www.fixando.pt/servicos/instalacao-de-painel-solar' },
    
    // Mudanças
    { name: 'Mudancas', url: 'https://www.fixando.pt/servicos/mudancas' },
    
    // Handyman
    { name: 'Handyman', url: 'https://www.fixando.pt/servicos/handyman' },
    { name: 'Biscates', url: 'https://www.fixando.pt/servicos/biscates-em-casa' },
    { name: 'Montagem-Moveis', url: 'https://www.fixando.pt/servicos/montagem-de-mobilia' },
    
    // Limpeza
    { name: 'Limpeza-Lixo-Entulho', url: 'https://www.fixando.pt/servicos/remocao-de-lixo-e-entulho' },
    
    // Estores/Persianas
    { name: 'Estores', url: 'https://www.fixando.pt/servicos/instalacao-de-estores-ou-persianas' },
    
    // Portas/Janelas
    { name: 'Janelas', url: 'https://www.fixando.pt/servicos/instalacao-de-janelas-de-aluminio' },
    
    // Decks/Terraços
    { name: 'Deck', url: 'https://www.fixando.pt/servicos/instalacao-de-deck' },
    { name: 'Terraco', url: 'https://www.fixando.pt/servicos/construcao-de-terraco' },
];

(async () => {
    const browser = await chromium.connectOverCDP('http://localhost:9222');
    const context = browser.contexts()[0];
    let page = context.pages()[0] || await context.newPage();

    const results = [];
    let successCount = 0;
    let failCount = 0;

    for (const svc of SERVICES) {
        console.log(`\n### ${svc.name} ###`);
        
        try {
            await page.goto(svc.url, { waitUntil: 'networkidle', timeout: 15000 });
            await page.waitForTimeout(3000);
        } catch(e) {
            console.log(`  LOAD ERROR: ${e.message}`);
            failCount++;
            continue;
        }

        // Try to find the wizard
        const body = await page.locator('body').innerText();
        const hasWizard = body.includes('minutos de completar');
        
        if (!hasWizard) {
            // Try clicking "Pedir serviço"
            const pedir = page.locator('button:has-text("Pedir")');
            if (await pedir.count() > 0) {
                try {
                    await pedir.first().click();
                    await page.waitForTimeout(4000);
                } catch(e) {}
                const body2 = await page.locator('body').innerText();
                if (!body2.includes('minutos de completar')) {
                    console.log(`  NO WIZARD`);
                    failCount++;
                    continue;
                }
            } else {
                console.log(`  NO WIZARD, no Pedir btn`);
                failCount++;
                continue;
            }
        }

        // Extract questions
        const qs = await extractQuestions(page, svc.name);
        if (qs.length === 0) {
            console.log(`  WIZARD but no questions extracted`);
            failCount++;
            continue;
        }

        console.log(`  GOT ${qs.length} questions`);
        results.push({ service: svc.name, questions: qs });
        successCount++;
    }

    // Save markdown
    let md = '# Fixando Complete Form Research\n\n';
    md += `Services with wizard: ${successCount} | No wizard: ${failCount}\n\n`;
    md += '---\n\n';

    for (const r of results) {
        md += `## ${r.service}\n\n`;
        for (let i = 0; i < r.questions.length; i++) {
            const q = r.questions[i];
            md += `**Q${i+1}: ${q.q}**\n\n`;
            for (const o of q.options) {
                md += `- ${o}\n`;
            }
            md += '\n';
        }
        md += '---\n\n';
    }

    fs.writeFileSync('fixando-all-forms.md', md);
    console.log(`\n\n=== DONE: ${successCount} services, ${failCount} failed ===`);
    await browser.close();
})();

async function extractQuestions(page, name) {
    const body = await page.locator('body').innerText();
    const lines = body.split('\n').map(l => l.trim()).filter(l => l.length > 2);
    const wizardStart = lines.findIndex(l => l.includes('minutos de completar'));
    if (wizardStart < 0) return [];
    
    const wizardLines = lines.slice(wizardStart, wizardStart + 80);
    const questions = [];
    let currentQ = null;
    const skipWords = ['minutos', 'completar', 'Opcional', 'Recomendado', 'Obrigatório',
        'Continuar', 'Localização', 'Usar localização', 'Adicionar fotos', 'Outras informações',
        'Com mais detalhes', 'Apenas mostrarmos', 'Usamos o código-postal',
        'Os especialistas têm acesso', 'Para que precisamos'];

    for (const line of wizardLines) {
        const t = line.trim();
        if (!t || t.length < 3) continue;
        
        // Skip metadata lines
        if (skipWords.some(w => t.includes(w))) continue;
        if (/^\d+\s+pessoas/i.test(t)) continue;
        if (/^(Continuar|Seguinte|Submeter|Anterior)$/.test(t)) continue;
        
        // Question detection
        if (t.includes('?') || /^(Qual|Que|De que|Quantos|Como|Onde|Quando|Tem|Pretende|Deseja|Quer|Qual é|Há|Já|Vai|Precisa|Existe|Em que|Que tipo)/i.test(t)) {
            if (currentQ && currentQ.options.length > 0) questions.push(currentQ);
            currentQ = { q: t, options: [] };
        } else if (currentQ && t.length > 3 && t.length < 150 && !t.match(/^\d+$/)) {
            currentQ.options.push(t);
        } else if (currentQ && currentQ.options.length > 0 && t.length <= 3) {
            questions.push(currentQ);
            currentQ = null;
        }
    }
    if (currentQ && currentQ.options.length > 0) questions.push(currentQ);
    
    return questions;
}
