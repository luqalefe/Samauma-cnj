<x-filament-panels::page>
    {{-- Filters --}}
    <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Eixo</label>
            <select wire:model.live="eixoFilter"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todos os Eixos</option>
                @foreach($eixos as $eixo)
                    <option value="{{ $eixo }}">{{ $eixo }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Setor</label>
            <select wire:model.live="setorFilter"
                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option value="">Todos os Setores</option>
                @foreach($setores as $id => $nome)
                    <option value="{{ $id }}">{{ $nome }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Eixo Groups --}}
    @forelse($eixoGroups as $eixoName => $group)
        <div class="mb-8">
            {{-- Eixo Header --}}
            <div class="mb-4 flex items-center justify-between rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $eixoName }}</h2>
                    <p class="text-sm text-gray-500">
                        {{ $group['concluidos'] }} de {{ $group['total'] }} itens concluídos
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="h-3 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                        <div class="h-full rounded-full transition-all duration-500
                             {{ $group['progresso'] >= 70 ? 'bg-emerald-500' : ($group['progresso'] >= 40 ? 'bg-amber-500' : 'bg-rose-500') }}"
                             style="width: {{ $group['progresso'] }}%">
                        </div>
                    </div>
                    <span class="text-sm font-semibold {{ $group['progresso'] >= 70 ? 'text-emerald-600' : ($group['progresso'] >= 40 ? 'text-amber-600' : 'text-rose-600') }}">
                        {{ $group['progresso'] }}%
                    </span>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Artigo</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Requisito</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Setor</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Pts</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Tarefas</th>
                            <th class="px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($group['itens'] as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $item->artigo }}</td>
                                <td class="max-w-xs truncate px-4 py-3 text-gray-600 dark:text-gray-400" title="{{ $item->requisito }}">
                                    {{ Str::limit($item->requisito, 60) }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->setor)
                                        <span class="inline-flex items-center rounded-md bg-primary-50 px-2 py-1 text-xs font-medium text-primary-700 ring-1 ring-inset ring-primary-600/20 dark:bg-primary-400/10 dark:text-primary-400">
                                            {{ $item->setor->sigla }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400">{{ $item->pontos_maximos }}</td>
                                <td class="px-4 py-3">
                                    @if($item->tarefas_count > 0)
                                        <span class="text-xs">
                                            <span class="text-emerald-600">{{ $item->tarefas_concluidas_count }}</span>
                                            /{{ $item->tarefas_count }}
                                            @if($item->tarefas_atrasadas_count > 0)
                                                <span class="ml-1 text-rose-600">({{ $item->tarefas_atrasadas_count }} atrasadas)</span>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-xs">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium
                                        {{ $item->status->value === 'concluido' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-400' :
                                           ($item->status->value === 'em_andamento' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20 dark:bg-amber-400/10 dark:text-amber-400' :
                                           'bg-gray-50 text-gray-700 ring-1 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-400') }}">
                                        {{ $item->status->getLabel() }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-xl bg-white p-12 text-center shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <x-heroicon-o-clipboard-document-list class="mx-auto h-12 w-12 text-gray-400"/>
            <p class="mt-4 text-gray-500">Nenhum item encontrado com os filtros selecionados.</p>
        </div>
    @endforelse
</x-filament-panels::page>
