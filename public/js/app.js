
document.addEventListener('click', (e)=>{
  if(e.target.matches('.monster-option input')){
    e.target.closest('.monster-option').classList.add('active');
  }
});

/**
     * Aplica a máscara de telefone celular (XX) XXXXX-XXXX e permite apenas números.
     * @param {HTMLInputElement} input - O elemento input.
     */
    function phoneMask(input) {
        // 1. Remove tudo que não for dígito e garante que o máximo é 11 dígitos
        // (2 de DDD + 9 do celular)
        let value = input.value.replace(/\D/g, '').substring(0, 11); 

        // 2. Aplica a máscara
        if (value.length > 0) {
            value = '(' + value; // (XX
        }
        if (value.length > 3) {
            value = value.substring(0, 3) + ') ' + value.substring(3); // (XX) XXXX
        }
        if (value.length > 10) {
            // Se tiver 5 dígitos após o DDD (celular), adiciona o traço na posição correta
            value = value.substring(0, 10) + '-' + value.substring(10, 15); // (XX) XXXXX-XXXX
        } else if (value.length > 9) {
            // Se tiver 4 dígitos após o DDD (telefone fixo, mas aqui adaptamos para a máscara de 10)
            // Esta parte é mais para compatibilidade, mas o foco é o celular de 9 dígitos.
            value = value.substring(0, 9) + '-' + value.substring(9, 14); // (XX) XXXX-XXXX (opcional, se quiser suportar)
        }
        
        // 3. Atualiza o valor do input
        input.value = value;
    }