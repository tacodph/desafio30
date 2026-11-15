export type ConsultationMeal = { id: number; title: string; sides: string[]; crop?: { viewBox: string; width: number; height: number } };
export type ConsultationMealGroup = { id: string; title: string; meals: ConsultationMeal[] };

export const consultationMealGroups: ConsultationMealGroup[] = [
    {
        id: 'ovos', title: 'Opções com ovos — O básico que funciona',
        meals: [
            { id: 50, title: 'Arroz com vegetais e ovo', sides: ['Ervilhas, tomate e ervas', 'Ovo por cima do arroz'] },
            { id: 51, title: 'Omelete recheada com folhas', sides: ['Folhas verdes no recheio'] },
            { id: 5, title: 'Ovos com arroz e tomate', sides: ['Arroz com ervas', 'Tomate picado'] },
            { id: 8, title: 'Hambúrguer com ovo e legumes', sides: ['Ovo por cima da carne', 'Legumes picados'] },
            { id: 12, title: 'Mistura de ovos, cenoura e folhas', sides: ['Cenoura em tiras', 'Folhas picadas'] },
            { id: 13, title: 'Ovos e legumes com salada', sides: ['Cenoura em tiras', 'Folhas e tomate'] },
            { id: 23, title: 'Salada de folhas com ovos', sides: ['Ovos em pedaços', 'Folhas variadas'], crop: { viewBox: '0 335 960 900', width: 960, height: 1280 } },
            { id: 49, title: 'Omelete com azeitonas e vegetais', sides: ['Azeitonas em rodelas', 'Milho e ervas'] },
            { id: 33, title: 'Omelete com salada de tomate', sides: ['Folhas verdes', 'Tomate picado'] },
            { id: 45, title: 'Omelete com arroz e salada', sides: ['Arroz', 'Abacate e tomate'], crop: { viewBox: '45 400 675 800', width: 720, height: 1280 } },
            { id: 66, title: 'Ovos com folhas verdes', sides: ['Folhas verdes', 'Ovos preparados na frigideira'] },
            { id: 67, title: 'Inspiração de refeição com ovos', sides: [] },
        ],
    },
    {
        id: 'paes', title: 'Opções de lanches com pão',
        meals: [
            { id: 18, title: 'Sanduíche com proteína desfiada e salada', sides: ['Pão com sementes', 'Folhas e cebola-roxa'], crop: { viewBox: '500 0 780 765', width: 1280, height: 765 } },
            { id: 27, title: 'Lanche de carne com salada', sides: ['Queijo e folhas no recheio', 'Outra apresentação do lanche'] },
            { id: 30, title: 'Pão recheado com frango e vegetais', sides: ['Recheio com folhas'], crop: { viewBox: '95 485 510 510', width: 720, height: 1280 } },
            { id: 39, title: 'Ovos com pão e tomate', sides: ['Ovos com ervas', 'Pão e tomate à parte'] },
            { id: 44, title: 'Sanduíche em camadas com salada', sides: ['Folhas e tomate', 'Recheio cremoso'] },
            { id: 52, title: 'Pão tipo wrap recheado com tomate e queijo', sides: ['Tomate e ervas', 'Recheio com queijo'] },
            { id: 53, title: 'Sanduíche com recheio cremoso e tomate', sides: ['Tomate, cebola-roxa e ervas', 'Pão de forma'] },
            { id: 17, title: 'Lanche com frango, tomate e cebola-roxa', sides: ['Folhas verdes'] },
        ],
    },
    {
        id: 'pratos', title: 'OPÇÕES COM ARROZ — SIM, ARROZ TAMBÉM PODE SER JANTAR!',
        meals: [
            { id: 14, title: 'Carne com arroz, feijão e salada', sides: ['Arroz e feijão-preto', 'Folhas e tomate'] },
            { id: 15, title: 'Bolinhos com feijão e folhas', sides: ['Arroz', 'Salada de folhas'], crop: { viewBox: '210 0 820 576', width: 1280, height: 576 } },
            { id: 36, title: 'Carne em cubos com arroz e legumes', sides: ['Arroz', 'Legumes picados com milho'] },
            { id: 47, title: 'Carne com arroz e legumes', sides: ['Arroz com cenoura', 'Abobrinha em cubos'] },
            { id: 57, title: 'Carne moída com arroz, feijão e tomate', sides: ['Arroz e feijão', 'Tomate picado'], crop: { viewBox: '30 170 900 900', width: 960, height: 1280 } },
            { id: 58, title: 'Frango com arroz e brócolis', sides: ['Arroz com cenoura', 'Brócolis'], crop: { viewBox: '10 280 700 700', width: 720, height: 1280 } },
            { id: 59, title: 'Frango com arroz, feijão e tomate', sides: ['Arroz e feijão', 'Tomate picado'] },
            { id: 60, title: 'Ovos cozidos com arroz e tomate', sides: ['Arroz', 'Tomate e cebolinha'] },
            { id: 61, title: 'Frango com arroz, feijão e vegetais', sides: ['Arroz e feijão', 'Alface, tomate e legumes'] },
            { id: 62, title: 'Omelete com arroz, feijão e repolho-roxo', sides: ['Arroz e feijão', 'Repolho-roxo'] },
            { id: 63, title: 'Filé com arroz, batatas e legumes', sides: ['Arroz e batatas', 'Cenoura, brócolis e couve-flor'] },
            { id: 65, title: 'Carne com arroz, feijão e vegetais', sides: ['Arroz e feijão', 'Legumes variados e salada'] },
        ],
    },
    {
        id: 'massas', title: 'Massas com proteínas e vegetais',
        meals: [
            { id: 3, title: 'Espaguete com carne moída e brócolis', sides: ['Carne ao molho', 'Brócolis'] },
            { id: 4, title: 'Penne com frango e legumes', sides: ['Frango desfiado', 'Abóbora, tomate e cubos de queijo'] },
            { id: 9, title: 'Espaguete com carne moída e salada', sides: ['Folhas e tomate'] },
            { id: 20, title: 'Macarrão com carne e brócolis', sides: ['Tomate-cereja', 'Brócolis'] },
            { id: 38, title: 'Filés com macarrão e legumes', sides: ['Massa em espiral', 'Legumes picados'] },
            { id: 43, title: 'Massa, frango e vegetais para combinar', sides: ['Abóbora em cubos', 'Salada de folhas e tomate'] },
            { id: 56, title: 'Macarrão com carne moída e folhas', sides: ['Carne moída servida separadamente', 'Folhas verdes e ervas'] },
            { id: 64, title: 'Macarrão com proteína e cebolinha', sides: ['Massa com proteína', 'Cebolinha picada'] },
        ],
    },

    {
        id: 'sopas', title: 'SOPAS E CALDOS',
        meals: [
            { id: 16, title: 'Sopa de legumes em pedaços', sides: ['Cenoura e outros legumes', 'Ervas no caldo'] },
            { id: 22, title: 'Creme de legumes com pão', sides: ['Ervas para finalizar', 'Fatia de pão'] },
            { id: 42, title: 'Sopa cremosa com pão', sides: ['Ervas picadas', 'Pão ao lado'] },
            { id: 54, title: 'Sopa de frango com legumes e pão', sides: ['Frango desfiado, cenoura e legumes', 'Pão como acompanhamento'] },
        ],
    },

    {
        id: 'outros', title: 'Outras opções',
        meals: [
            { id: 19, title: 'Espetinho de frango ou carne magra (contrafilé sem gordura ou alcatra)', sides: ['Acompanhe com vinagrete e escolha mandioca cozida ou arroz como carboidrato.', 'Evite farofas, feijão tropeiro e frituras no geral.'] },
            { id: 46, title: 'Patê de frango com chips de batata caseiros', sides: ['Prepare o patê com frango, requeijão light e temperos.', 'Os chips podem ser feitos com qualquer tubérculo: inhame, cará, batata-doce, batata inglesa ou batata-baroa.'] },
            { id: 34, title: 'Bolinho de mandioca com frango', sides: ['Também pode ser feito com qualquer outro tubérculo.', 'Processe o tubérculo cozido e o frango cozido, molde em forma de bolinhos e asse na air fryer.'] },
            { id: 35, title: '', sides: ['Essa opção pode ser congelada para usar em outra oportunidade e facilitar o seu dia a dia.'] },
            { id: 10, title: 'Preparações da vida social com salada', sides: ['Ao escolher uma opção mais gordurosa, como uma lasanha ou um risoto, complemente o restante do prato com salada.'] },
            { id: 21, title: 'Uma opção para pedir no iFood', sides: ['Siga a mesma lógica de um prato equilibrado: inclua vegetais e uma fonte de proteína magra.', 'Escolha uma refeição de verdade, como uma marmita com salada, legumes e proteína magra, em vez de fast food.'] },
        ],
    },
];

export const consultationMealImage = (id: number) => `/images/alimentacao/refeicoes_reais/refeicao_real%20(${id}).jpeg`;
