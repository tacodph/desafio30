export type ConsultationMeal = { id: number; title: string; sides: string[] };
export type ConsultationMealGroup = { id: string; title: string; meals: ConsultationMeal[] };

export const consultationMealGroups: ConsultationMealGroup[] = [
    {
        id: 'pratos', title: 'Pratos com acompanhamentos',
        meals: [
            { id: 5, title: 'Ovos com arroz e tomate', sides: ['Arroz com ervas', 'Tomate picado'] },
            { id: 6, title: 'Carne com batatas e salada', sides: ['Batatas em duas apresentações', 'Folhas e tomate'] },
            { id: 8, title: 'Hambúrguer com ovo e legumes', sides: ['Ovo por cima da carne', 'Legumes picados'] },
            { id: 10, title: 'Preparação gratinada com salada', sides: ['Folhas, tomate e cenoura'] },
            { id: 13, title: 'Ovos e legumes com salada', sides: ['Cenoura em tiras', 'Folhas e tomate'] },
            { id: 14, title: 'Carne com arroz, feijão e salada', sides: ['Arroz e feijão-preto', 'Folhas e tomate'] },
            { id: 15, title: 'Bolinhos com feijão e folhas', sides: ['Arroz', 'Salada de folhas'] },
            { id: 21, title: 'Marmita com filé e legumes', sides: ['Arroz e feijão', 'Legumes variados e purê'] },
            { id: 36, title: 'Carne em cubos com arroz e legumes', sides: ['Arroz', 'Legumes picados com milho'] },
            { id: 37, title: 'Filé com abobrinha e batatas', sides: ['Abobrinha em rodelas', 'Batatas douradas'] },
            { id: 45, title: 'Omelete com arroz e salada', sides: ['Arroz', 'Abacate e tomate'] },
            { id: 47, title: 'Carne com arroz e legumes', sides: ['Arroz com cenoura', 'Abobrinha em cubos'] },
        ],
    },
    {
        id: 'massas', title: 'Massas com proteínas e vegetais',
        meals: [
            { id: 3, title: 'Espaguete com carne moída e brócolis', sides: ['Carne ao molho', 'Brócolis'] },
            { id: 4, title: 'Penne com frango e legumes', sides: ['Frango desfiado', 'Abóbora, tomate e cubos de queijo'] },
            { id: 7, title: 'Penne com frango e vegetais', sides: ['Tomate e cenoura', 'Ervilhas e ervas'] },
            { id: 9, title: 'Espaguete com carne moída e salada', sides: ['Folhas e tomate'] },
            { id: 20, title: 'Macarrão com carne e brócolis', sides: ['Tomate-cereja', 'Brócolis'] },
            { id: 38, title: 'Filés com macarrão e legumes', sides: ['Massa em espiral', 'Legumes picados'] },
            { id: 40, title: 'Penne com frango, abóbora e queijo', sides: ['Tomate-cereja', 'Ingredientes antes de misturar'] },
            { id: 41, title: 'Penne com frango e legumes coloridos', sides: ['Tomate, cenoura e ervilhas', 'Refeição pronta para servir'] },
            { id: 43, title: 'Massa, frango e vegetais para combinar', sides: ['Abóbora em cubos', 'Salada de folhas e tomate'] },
        ],
    },
    {
        id: 'lanches', title: 'Lanches, omeletes e saladas',
        meals: [
            { id: 1, title: 'Omelete recheada com folhas', sides: ['Folhas verdes no recheio'] },
            { id: 17, title: 'Omelete com tomate e cebola-roxa', sides: ['Folhas verdes'] },
            { id: 18, title: 'Sanduíche com proteína desfiada e salada', sides: ['Pão com sementes', 'Folhas e cebola-roxa'] },
            { id: 19, title: 'Rolinho recheado com salada', sides: ['Salada picada em acompanhamento'] },
            { id: 23, title: 'Salada de folhas com ovos', sides: ['Ovos em pedaços', 'Folhas variadas'] },
            { id: 25, title: 'Lanche de frigideira com carne e folhas', sides: ['Queijo no recheio', 'Salada à parte'] },
            { id: 26, title: 'Lanche recheado com carne e queijo', sides: ['Folhas verdes', 'Salada em tigela'] },
            { id: 27, title: 'Lanche de carne com salada', sides: ['Queijo e folhas no recheio', 'Outra apresentação do lanche'] },
            { id: 29, title: 'Torrada com ovos e tomate-cereja', sides: ['Folhas verdes', 'Tomates como acompanhamento'] },
            { id: 30, title: 'Pão recheado com ovos e vegetais', sides: ['Recheio com folhas'] },
            { id: 31, title: 'Sanduíche de ovos com folhas', sides: ['Pão', 'Ovos temperados no recheio'] },
            { id: 32, title: 'Salada de frango com massa de frigideira', sides: ['Frango desfiado e folhas', 'Massa servida à parte'] },
            { id: 33, title: 'Omelete com salada de tomate', sides: ['Folhas verdes', 'Tomate picado'] },
            { id: 39, title: 'Ovos com pão e tomate', sides: ['Ovos com ervas', 'Pão e tomate à parte'] },
            { id: 44, title: 'Sanduíche em camadas com salada', sides: ['Folhas e tomate', 'Recheio cremoso'] },
        ],
    },
    {
        id: 'sopas', title: 'Sopas e cremes',
        meals: [
            { id: 11, title: 'Sopa com cenoura ralada', sides: ['Cenoura por cima', 'Tomate como complemento'] },
            { id: 16, title: 'Sopa de legumes em pedaços', sides: ['Cenoura e outros legumes', 'Ervas no caldo'] },
            { id: 22, title: 'Creme de legumes com pão', sides: ['Ervas para finalizar', 'Fatia de pão'] },
            { id: 24, title: 'Creme alaranjado com ervas', sides: ['Pão como acompanhamento'] },
            { id: 28, title: 'Creme de legumes com torrada', sides: ['Ervas por cima'] },
            { id: 42, title: 'Sopa cremosa com pão', sides: ['Ervas picadas', 'Pão ao lado'] },
        ],
    },
    {
        id: 'tigelas', title: 'Misturas em tigela e acompanhamentos',
        meals: [
            { id: 2, title: 'Frango cremoso com batatas', sides: ['Frango desfiado e ervas', 'Batatas em rodelas'] },
            { id: 12, title: 'Mistura de ovos, cenoura e folhas', sides: ['Cenoura em tiras', 'Folhas picadas'] },
            { id: 46, title: 'Tigela de frango com batatas douradas', sides: ['Mistura cremosa com ervas', 'Batatas à parte'] },
            { id: 48, title: 'Frango desfiado cremoso com batatas', sides: ['Ervas na mistura', 'Outra apresentação da combinação'] },
        ],
    },
    {
        id: 'bolinhos', title: 'Bolinhos e preparações práticas',
        meals: [
            { id: 34, title: 'Bolinhos dourados com ervas', sides: ['Porções individuais', 'Ervas na massa'] },
            { id: 35, title: 'Bolinhos com vegetais', sides: ['Porções organizadas em recipiente', 'Vegetais e ervas na massa'] },
        ],
    },
];

export const consultationMealImage = (id: number) => `/images/alimentacao/refeicoes_reais/refeicao_real%20(${id}).jpeg`;
