const CHAVE_JOGOS = 'gameshelf_jogos';
const CHAVE_WISHLIST = 'gameshelf_wishlist';
const CHAVE_REVIEWS = 'gameshelf_reviews';
const CHAVE_PERFIL = 'gameshelf_perfil';
const CHAVE_TEMA = 'gameshelf_tema';

function carregarDados(chave, padrao = []) {
  const dados = localStorage.getItem(chave);

  if (!dados) {
    return padrao;
  }

  try {
    return JSON.parse(dados);
  } catch (erro) {
    return padrao;
  }
}

function salvarDados(chave, objeto) {
  localStorage.setItem(chave, JSON.stringify(objeto));
}

function gerarId() {
  return 'id_' + Math.random().toString(36).substring(2, 9) + '_' + Date.now();
}

function generarId() {
  return gerarId();
}

function aplicarTemaGlobal() {
  const tema = carregarDados(CHAVE_TEMA, 'escuro');

  if (tema === 'claro') {
    document.body.classList.add('tema-claro');
  } else {
    document.body.classList.remove('tema-claro');
  }
}

function escaparHTML(valor) {
  return String(valor ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
}

function formatarStatus(status) {
  const nomes = {
    jogando: 'Jogando',
    zerado: 'Zerado',
    pausado: 'Pausado',
    abandonado: 'Abandonado',
    'nao-iniciado': 'Não iniciado'
  };

  return nomes[status] || 'Não informado';
}

function formatarPrioridade(prioridade) {
  const nomes = {
    alta: 'Alta',
    media: 'Média',
    baixa: 'Baixa'
  };

  return nomes[prioridade] || 'Média';
}

function gerarCapaPadrao(titulo = 'GameShelf') {
  const texto = escaparHTML(titulo).substring(0, 28);
  const svg = `
    <svg xmlns="http://www.w3.org/2000/svg" width="320" height="460" viewBox="0 0 320 460">
      <defs>
        <linearGradient id="g" x1="0" y1="0" x2="1" y2="1">
          <stop offset="0%" stop-color="#7c00ff"/>
          <stop offset="48%" stop-color="#121225"/>
          <stop offset="100%" stop-color="#00f5ff"/>
        </linearGradient>
        <filter id="glow">
          <feGaussianBlur stdDeviation="4" result="coloredBlur"/>
          <feMerge>
            <feMergeNode in="coloredBlur"/>
            <feMergeNode in="SourceGraphic"/>
          </feMerge>
        </filter>
      </defs>
      <rect width="320" height="460" rx="26" fill="#090913"/>
      <rect x="12" y="12" width="296" height="436" rx="24" fill="url(#g)" opacity="0.95"/>
      <rect x="30" y="30" width="260" height="400" rx="18" fill="rgba(5,5,14,0.74)" stroke="#00f5ff" stroke-width="2"/>
      <path d="M65 125 H255 M65 335 H255" stroke="#7c00ff" stroke-width="5" opacity="0.8"/>
      <text x="160" y="205" text-anchor="middle" font-family="Arial" font-size="30" font-weight="800" fill="#ffffff" filter="url(#glow)">GameShelf</text>
      <text x="160" y="254" text-anchor="middle" font-family="Arial" font-size="19" font-weight="700" fill="#00f5ff">${texto}</text>
      <text x="160" y="392" text-anchor="middle" font-family="Arial" font-size="13" fill="#ffffff" opacity="0.78">CAPA PADRÃO</text>
    </svg>
  `;

  return 'data:image/svg+xml;charset=UTF-8,' + encodeURIComponent(svg);
}

function converterImagemBase64(arquivo) {
  return new Promise((resolve, reject) => {
    const leitor = new FileReader();

    leitor.onload = function () {
      resolve(leitor.result);
    };

    leitor.onerror = function () {
      reject('Erro ao carregar imagem');
    };

    leitor.readAsDataURL(arquivo);
  });
}

function carregarPerfilPadrao() {
  return {
    nome: '',
    avatar: '',
    bio: '',
    plataforma: '',
    genero: '',
    gamertag: '',
    perfilExterno: '',
    totalJogosExternos: '',
    horasJogadas: '',
    conquistas: '',
    jogosCompletos: '',
    perfilVerificado: '',
    plataformaPrincipal: '',
    generosPrincipais: '',
    playstation: '',
    xbox: '',
    pc: '',
    nintendo: ''
  };
}

function obterJogos() {
  return carregarDados(CHAVE_JOGOS, []);
}

function salvarJogos(jogos) {
  salvarDados(CHAVE_JOGOS, jogos);
}

function obterWishlist() {
  return carregarDados(CHAVE_WISHLIST, []);
}

function salvarWishlist(lista) {
  salvarDados(CHAVE_WISHLIST, lista);
}

function obterReviews() {
  return carregarDados(CHAVE_REVIEWS, []);
}

function salvarReviews(reviews) {
  salvarDados(CHAVE_REVIEWS, reviews);
}

document.addEventListener('DOMContentLoaded', aplicarTemaGlobal);
