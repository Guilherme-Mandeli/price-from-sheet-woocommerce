# WooCommerce Price From Sheet

Plugin WordPress para importação em massa de preços de produtos WooCommerce através de planilhas CSV e Excel.

---

## 📋 Visão Geral

O **Price From Sheet | WooCommerce** é um plugin que permite atualizar preços de centenas ou milhares de produtos WooCommerce de uma só vez, utilizando arquivos CSV ou Excel.
Em vez de alterar produtos manualmente um por um, você pode fazer tudo em apenas alguns cliques!

---

## 🎯 Funcionalidades Principais

- ✅ Importação em massa de preços via CSV (`.csv`) ou Excel (`.xlsx`, `.xls`)  
- ✅ Suporte a preços promocionais (`sale_price`)  
- ✅ Dois modos de atualização: atualizar preços existentes ou apenas adicionar novos  
- ✅ Interface intuitiva integrada ao painel do WooCommerce  
- ✅ Validação robusta de dados com relatórios detalhados de erros  
- ✅ Templates prontos para download (CSV e Excel)  
- ✅ Suporte completo ao UTF-8 para caracteres especiais  
- ✅ Compatibilidade com HPOS (High-Performance Order Storage)  

---

## 🚀 Instalação

### Requisitos

- WordPress 5.0+  
- WooCommerce 5.0+  
- PHP 7.4+  
- Extensões PHP: `zip`, `xml`  

### Passos de Instalação

1. Clone o repositório na pasta plugin do seu site WordPress:
```
git clone https://github.com/Guilherme-Mandeli/price-from-sheet-woocommerce.git
```
ou via SSH
```
git clone git@github.com:Guilherme-Mandeli/price-from-sheet-woocommerce.git
```
ou importe o arquivo ZIP como plugin no WordPress.
2. Ative o plugin no painel do WordPress em **Plugins > Plugins Instalados**.  
3. Acesse o plugin em **WooCommerce > Price From Sheet**.

---

## 📊 Como Usar

### 1. Preparando sua Planilha

**Formato Básico (Obrigatório)**

| sku      | price |
|----------|-------|
| PROD-001 | 29.90 |
| PROD-002 | 15.50 |
| PROD-003 | 89.99 |

**Formato Completo (Com Preço Promocional)**

| sku      | price | sale_price |
|----------|-------|------------|
| PROD-001 | 29.90 | 24.90      |
| PROD-002 | 15.50 |            |
| PROD-003 | 89.99 | sem valor  |

**Palavras-chave suportadas**

Coluna: sale_price
- null, none, empty
- nulo, vazio, nenhum, sem valor
- vacío, ninguno,sin valor

As palavras chaves acima removerão o valor do preço promocional

**Regras Importantes**

- **SKU:** Deve ser exatamente igual ao cadastrado no WooCommerce  
- **Preços:** Use ponto (`.`) como separador decimal (ex: 29.90)  
- **Arquivo:** Salve como CSV (separado por vírgulas) ou Excel  
- **Codificação:** UTF-8 para evitar problemas com acentos  
- **Tamanho máximo:** 10MB  

### 2. Importando Preços

1. Escolha o arquivo: Clique em **Escolher arquivo** e selecione sua planilha  
2. Selecione o modo:
   - **Atualizar preços existentes:** Define o novo valor para todos os produtos listados  
   - **Apenas adicionar novos preços:** Define valor apenas para produtos sem preço definido  
3. Execute a importação: Clique em **Importar Agora** e aguarde o processo  

### 3. Templates Prontos

O plugin oferece templates prontos para download:

- **Template CSV:** Formato básico para importação  
- **Template Excel:** Formato Excel com estrutura correta  

Acesse em **WooCommerce > Price From Sheet** e clique nos botões de download.

---

## 🏗️ Arquitetura do Sistema

### Estrutura de Arquivos

**Classes Principais**

- **WCPFS_Main**  
  - Inicialização do plugin  
  - Carregamento de scripts e estilos  
  - Gerenciamento de hooks do WordPress  

- **WCPFS_Admin**  
  - Interface administrativa  
  - Geração de templates  
  - Manipulação de requisições AJAX  
  - Página de configurações e guia completo  

- **WCPFS_Importer**  
  - Processamento de arquivos CSV e Excel  
  - Validação de dados  
  - Atualização de produtos no WooCommerce  
  - Geração de relatórios de erro  

---

## 🔧 Funcionalidades Técnicas

**Processamento de Arquivos**

- CSV: Leitura nativa com `fgetcsv()`  
- Excel: Utiliza PHPSpreadsheet para arquivos `.xlsx/.xls`  
- Validação: Verificação de formato, tamanho e estrutura  
- Encoding: Suporte completo a UTF-8 com remoção de BOM  

**Validações Implementadas**

- ✅ Verificação de SKU existente no WooCommerce  
- ✅ Validação de formato de preços (números positivos)  
- ✅ Verificação de preço promocional menor que preço regular  
- ✅ Detecção de linhas vazias ou incompletas  
- ✅ Relatório detalhado de erros com número da linha  

**Segurança**

- ✅ Verificação de nonces em todas as requisições AJAX  
- ✅ Validação de permissões (`manage_woocommerce`)  
- ✅ Sanitização de dados de entrada  
- ✅ Prevenção de acesso direto aos arquivos  

---

## 🐛 Solução de Problemas

**Erros Comuns**

- **"Produto com SKU não encontrado"**  
  Causa: SKU na planilha não existe no WooCommerce  
  Solução: Verificar se o SKU está correto e existe no sistema  

- **"Linha inválida: SKU ou preço não encontrado"**  
  Causa: Linha da planilha está incompleta  
  Solução: Garantir que todas as linhas tenham SKU e preço  

- **"Preço inválido"**  
  Causa: Preço não é um número válido  
  Solução: Usar apenas números com ponto como separador decimal  

**Boas Práticas**

- 🔄 Sempre faça backup antes de importar  
- 🧪 Teste pequeno: Comece com poucos produtos para testar  
- ✅ Verificar SKUs: Confirme se os SKUs estão corretos  
- 🔤 Usar UTF-8: Para evitar problemas com acentos  

---

## 📈 Exemplo Prático

**Cenário:** Aumento de 10% em 500 produtos

1. Exportar produtos atuais: Use **WooCommerce > Produtos > Exportar**  
2. Calcular novos preços: Abra no Excel/Google Sheets e crie fórmula para aumentar 10%  
3. Importar novos preços: Salve como CSV ou Excel e importe usando este plugin  

---

## 🔒 Segurança

- Validação de Nonce em todas as requisições AJAX  
- Verificação de permissões (`manage_woocommerce`)  
- Sanitização de todos os dados de entrada  
- Validação de arquivos: tipo, tamanho e estrutura  
- Prevenção de XSS: Escape de saída  

**Recomendações**

- Mantenha backups regulares  
- Teste em ambiente de desenvolvimento primeiro  
- Monitore logs de erro  
- Limite acesso apenas a usuários autorizados  

---

## 👥 Desenvolvimento

**Desenvolvedor Principal**

- Guilherme Mandeli  
  - 🌐 Website: [srmandeli.contact](https://srmandeli.contact)  
  - 📧 Email: guil.mandeli@gmail.com  

**Empresa**

- Hooma  
  - 🌐 Website: [hooma.com.br](https://hooma.com.br)  
  - 📧 Suporte: gmandeli@hooma.com.br

---

## 📄 Licença

Este plugin é licenciado sob a **GPL v2** ou posterior.

---

## 🔄 Changelog

**Versão 1.0.0 (2025-8-15)**

- ✨ Lançamento inicial  
- ✅ Suporte a importação CSV e Excel  
- ✅ Interface administrativa completa  
- ✅ Validações robustas  
- ✅ Templates para download  
- ✅ Suporte a preços promocionais  
- ✅ Compatibilidade com HPOS  

---

## 🆘 Suporte

**Canais de Suporte**

1. Documentação: Este README  
2. Website: [hooma.com.br](https://hooma.com.br)  
3. Issues: GitHub Issues (se aplicável)  
4. Email: Através do website da Hooma ou gmandeli@hooma.com.br

**FAQ**

- **Posso importar outros campos além do preço?**  
  Atualmente apenas preços regulares e promocionais. Outros campos estão no roadmap.  

- **Qual o limite de produtos por importação?**  
  Recomendamos até 5000 produtos. Para mais, divida em lotes menores.  

- **O plugin funciona com produtos variáveis?**  
  Sim, use o SKU específico de cada variação.  

- **Posso desfazer uma importação?**  
  Não há função de desfazer. Sempre faça backup antes de importar.  

💡 Precisa de ajuda? Visite [hooma.com.br](https://hooma.com.br) para suporte técnico.  

⭐ Gostou do plugin? Considere deixar uma avaliação e compartilhar com outros desenvolvedores!
