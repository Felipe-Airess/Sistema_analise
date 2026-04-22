# Documentação de Máscaras de Formulário

Todas as máscaras estão implementadas no arquivo `app/assets/js/masks.js` usando a biblioteca **IMask.js**.

## Scripts Necessários

Para usar as máscaras, adicione ao seu arquivo HTML:

```html
<!-- Biblioteca IMask -->
<script src="https://unpkg.com/imask"></script>

<!-- Arquivo com as máscaras -->
<script src="../assets/js/masks.js"></script>
```

## Máscaras Disponíveis

### 1. CNPJ
- **Formato:** `XX.XXX.XXX/XXXX-XX`
- **Uso automático:** Campo com `id="cnpj"` ou classe `mask-cnpj`
- **Uso manual:**
```html
<input type="text" id="cnpj" name="cnpj">
<script>
    maskCNPJ(document.getElementById('cnpj'));
</script>
```
- **Validação:** `isValidCNPJ(valor)`

### 2. CPF
- **Formato:** `XXX.XXX.XXX-XX`
- **Uso automático:** Campo com `id="cpf"` ou classe `mask-cpf`
- **Uso manual:**
```html
<input type="text" id="cpf" name="cpf">
<script>
    maskCPF(document.getElementById('cpf'));
</script>
```
- **Validação:** `isValidCPF(valor)`

### 3. Telefone
- **Formato:** `(XX) XXXXX-XXXX` ou `(XX) XXXX-XXXX` (automático)
- **Uso automático:** Campo com `id="telefone"` ou classe `mask-telephone`
- **Uso manual:**
```html
<input type="text" id="telefone" name="telefone">
<script>
    maskTelephone(document.getElementById('telefone'));
</script>
```
- **Validação:** `isValidTelephone(valor)`

### 4. CEP
- **Formato:** `XXXXX-XXX`
- **Uso automático:** Campo com `id="cep"` ou classe `mask-cep`
- **Uso manual:**
```html
<input type="text" id="cep" name="cep">
<script>
    maskCEP(document.getElementById('cep'));
</script>
```

### 5. Moeda (Real)
- **Formato:** `R$ 9.999,99`
- **Uso automático:** Campo com `id` contendo "valor", "salario" ou "valor_meta", ou classe `mask-currency`
- **Uso manual:**
```html
<input type="text" id="preco" name="preco">
<script>
    maskCurrency(document.getElementById('preco'));
</script>
```

## Funções Auxiliares

### `removeMask(value)`
Remove todos os caracteres não numéricos:
```javascript
removeMask('12.345.678/0001-00') // Retorna: '12345678000100'
```

### Validações

```javascript
// Validar CNPJ
if (isValidCNPJ(document.getElementById('cnpj').value)) {
    console.log('CNPJ válido');
}

// Validar CPF
if (isValidCPF(document.getElementById('cpf').value)) {
    console.log('CPF válido');
}

// Validar Telefone
if (isValidTelephone(document.getElementById('telefone').value)) {
    console.log('Telefone válido');
}
```

## Exemplo Completo - Formulário com Máscaras

```html
<!DOCTYPE html>
<html>
<head>
    <script src="https://unpkg.com/imask"></script>
    <script src="../assets/js/masks.js"></script>
</head>
<body>
    <form method="POST">
        <div>
            <label>CNPJ:</label>
            <input type="text" id="cnpj" name="cnpj" required>
        </div>

        <div>
            <label>Telefone:</label>
            <input type="text" id="telefone" name="telefone" required>
        </div>

        <div>
            <label>CPF:</label>
            <input type="text" id="cpf" name="cpf">
        </div>

        <div>
            <label>CEP:</label>
            <input type="text" id="cep" name="cep">
        </div>

        <div>
            <label>Valor:</label>
            <input type="text" id="valor" name="valor">
        </div>

        <button type="submit">Enviar</button>
    </form>
</body>
</html>
```

## Processamento no Backend (PHP)

As máscaras são apenas visuais. No backend, **remova os caracteres especiais** antes de salvar:

```php
$cnpj = preg_replace('/\D/', '', $_POST['cnpj']); // Remove tudo que não é número
$telefone = preg_replace('/\D/', '', $_POST['telefone']);
$cpf = preg_replace('/\D/', '', $_POST['cpf']);
$cep = preg_replace('/\D/', '', $_POST['cep']);
```

Ou use a função JavaScript `removeMask()` com AJAX antes de enviar:

```javascript
const formData = new FormData();
formData.append('cnpj', removeMask(document.getElementById('cnpj').value));
formData.append('telefone', removeMask(document.getElementById('telefone').value));

fetch('seu-endpoint.php', {
    method: 'POST',
    body: formData
});
```

## Personalização

Para adicionar novas máscaras, edite o arquivo `masks.js` seguindo o padrão:

```javascript
function maskSuaMaxcara(element) {
    if (element) {
        IMask(element, {
            mask: '00-00-00' // Seu padrão aqui
        });
    }
}
```

E adicione a inicialização automática:

```javascript
maskSuaMaxcara(document.getElementById('sua-id'));
// ou
document.querySelectorAll('.mask-sua-mascara').forEach(el => maskSuaMaxcara(el));
```
