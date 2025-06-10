{{-- Campo para selecionar a data de validade do alimento --}}
<div class="mb-4">
    <x-input-label for="validade" value="Data de Validade" />
    <x-text-input id="validade" type="date" name="validade" class="block mt-1 w-full" :value="old('validade', isset($alimento) ? $alimento->validade->format('Y-m-d') : '')" required />
    <x-input-error :messages="$errors->get('validade')" class="mt-2" />
</div>

{{-- Campo opcional para o usuário sugerir uma receita relacionada ao alimento --}}
<div class="mb-4">
    <x-input-label for="sugestao" value="Sugestão de Receita (opcional)" />
    <x-text-input id="sugestao" type="text" name="sugestao" class="block mt-1 w-full" :value="old('sugestao', isset($alimento) ? $alimento->sugestao : '')" placeholder="Digite uma sugestão de receita para este alimento" />
    <x-input-error :messages="$errors->get('sugestao')" class="mt-2" />
</div>

{{-- Área para os botões de ação do formulário --}}
<div class="flex items-center justify-end mt-4">