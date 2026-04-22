/**
 * Aplicar máscara de CNPJ
 * Formato: XX.XXX.XXX/XXXX-XX
 */
function maskCNPJ(element) {
    if (element) {
        IMask(element, {
            mask: '00.000.000/0000-00'
        });
    }
}

/**
 * Aplicar máscara de CPF
 * Formato: XXX.XXX.XXX-XX
 */
function maskCPF(element) {
    if (element) {
        IMask(element, {
            mask: '000.000.000-00'
        });
    }
}

/**
 * Aplicar máscara de Telefone
 * Formato: (XX) XXXXX-XXXX ou (XX) XXXX-XXXX
 */
function maskTelephone(element) {
    if (element) {
        IMask(element, {
            mask: [
                {
                    mask: '(00) 00000-0000',
                    startsWith: '9',
                    lazy: false
                },
                {
                    mask: '(00) 0000-0000',
                    lazy: false
                }
            ],
            dispatch(appended, dynamicMasked) {
                let phoneNumber = (dynamicMasked.value + appended).replace(/\D/g, '');
                let shouldUseFirstMask = phoneNumber[2] === '9';
                return dynamicMasked.compiledMasks[shouldUseFirstMask ? 0 : 1];
            }
        });
    }
}

/**
 * Aplicar máscara de CEP
 * Formato: XXXXX-XXX
 */
function maskCEP(element) {
    if (element) {
        IMask(element, {
            mask: '00000-000'
        });
    }
}

/**
 * Aplicar máscara de Moeda (Real)
 * Formato: R$ 9.999,99
 */
function maskCurrency(element) {
    if (element) {
        IMask(element, {
            mask: 'R$ num',
            blocks: {
                num: {
                    mask: Number,
                    thousandsSeparator: '.',
                    padFractionalZeros: true,
                    normalizeZeros: true,
                    radix: ',',
                    scale: 2
                }
            }
        });
    }
}

/**
 * Remover caracteres especiais (máscara)
 */
function removeMask(value) {
    return value.replace(/\D/g, '');
}

/**
 * Validar CNPJ
 */
function isValidCNPJ(cnpj) {
    cnpj = removeMask(cnpj);
    if (cnpj.length !== 14 || /^\d*$/.test(cnpj) === false) return false;
    
    let sum = 0;
    let remainder;
    
    for (let i = 0; i < 4; i++) {
        sum += cnpj[i] * (6 - i);
    }
    for (let i = 4; i < 8; i++) {
        sum += cnpj[i] * (10 - i);
    }
    remainder = sum % 11;
    if (remainder < 2) {
        remainder = 0;
    } else {
        remainder = 11 - remainder;
    }
    if (parseInt(cnpj[8]) !== remainder) return false;
    
    sum = 0;
    for (let i = 0; i < 5; i++) {
        sum += cnpj[i] * (7 - i);
    }
    for (let i = 5; i < 9; i++) {
        sum += cnpj[i] * (11 - i);
    }
    remainder = sum % 11;
    if (remainder < 2) {
        remainder = 0;
    } else {
        remainder = 11 - remainder;
    }
    if (parseInt(cnpj[9]) !== remainder) return false;
    
    return true;
}

/**
 * Validar CPF
 */
function isValidCPF(cpf) {
    cpf = removeMask(cpf);
    if (cpf.length !== 11 || /^\d*$/.test(cpf) === false) return false;
    
    let sum = 0;
    let remainder;
    
    for (let i = 1; i <= 9; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (11 - i);
    }
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.substring(9, 10))) return false;
    
    sum = 0;
    for (let i = 1; i <= 10; i++) {
        sum += parseInt(cpf.substring(i - 1, i)) * (12 - i);
    }
    remainder = (sum * 10) % 11;
    if (remainder === 10 || remainder === 11) remainder = 0;
    if (remainder !== parseInt(cpf.substring(10, 11))) return false;
    
    return true;
}

/**
 * Validar Telefone
 */
function isValidTelephone(telephone) {
    const digits = removeMask(telephone);
    return digits.length === 11;
}

// Inicializar máscaras quando o documento estiver pronto (se não usar esplicitamente)
document.addEventListener('DOMContentLoaded', function () {
    // Máscaras por ID
    maskCNPJ(document.getElementById('cnpj'));
    maskCPF(document.getElementById('cpf'));
    maskTelephone(document.getElementById('telefone'));
    maskCEP(document.getElementById('cep'));
    maskCurrency(document.getElementById('valor'));
    maskCurrency(document.getElementById('salario'));
    maskCurrency(document.getElementById('valor_meta'));
    
    // Máscaras por classe
    document.querySelectorAll('.mask-cnpj').forEach(el => maskCNPJ(el));
    document.querySelectorAll('.mask-cpf').forEach(el => maskCPF(el));
    document.querySelectorAll('.mask-telephone').forEach(el => maskTelephone(el));
    document.querySelectorAll('.mask-cep').forEach(el => maskCEP(el));
    document.querySelectorAll('.mask-currency').forEach(el => maskCurrency(el));
});
