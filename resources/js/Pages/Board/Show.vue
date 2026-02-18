<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const props = defineProps({
    board: Object,
    users: Array,
    isAdmin: Boolean,
});

import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentUser = page.props.auth.user;

const columns = ref(props.board.columns);
const newCardTitle = ref('');
const newCardDescription = ref('');
const newCardUserId = ref(null);
const newCardPriority = ref('medium');
const newCardDueDate = ref('');
const activeCardColumn = ref(null);
const activeToTodoCard = ref(false);

const newColumnName = ref('');
const isAddingColumn = ref(false);

const selectedCard = ref(null);
const showCardModal = ref(false);
const showMembersModal = ref(false);

const inviteUser = (userId) => {
    router.post(route('boards.add-member', props.board.id), {
        user_id: userId
    }, {
        preserveScroll: true
    });
};

const removeMember = (userId) => {
    if (confirm('Are you sure you want to remove this member?')) {
        router.delete(route('boards.remove-member', { id: props.board.id, user_id: userId }), {
            preserveScroll: true
        });
    }
};

const editForm = ref({
    title: '',
    description: '',
    user_id: null,
    priority: 'medium',
    due_date: '',
    status: '',
    dependencies: []
});

const canEdit = computed(() => {
    if (props.isAdmin) return true;
    if (!selectedCard.value) return false;
    return selectedCard.value.user_id === currentUser.id;
});

onMounted(() => {
    window.Echo.channel(`board.${props.board.id}`)
        .listen('CardMoved', (e) => {
            router.reload({ only: ['board'], preserveScroll: true });
        });
});

const moveCard = (cardId, toColumnId, newPosition) => {
    router.post(route('boards.move-card', props.board.id), {
        card_id: cardId,
        to_column_id: toColumnId,
        new_position: newPosition
    }, {
        preserveScroll: true,
        only: ['board', 'errors'],
        onError: (errors) => {
            if (errors.message) {
                alert(errors.message);
            }
        }
    });
};

const openCardModal = (card) => {
    selectedCard.value = card;
    const column = props.board.columns.find(c => c.id === card.column_id);
    editForm.value = {
        title: card.title,
        description: card.description || '',
        user_id: card.user_id,
        priority: card.priority || 'medium',
        due_date: card.due_date ? card.due_date.split('T')[0] : '',
        status: column?.status || '',
        dependencies: card.dependencies ? card.dependencies.map(d => d.id) : []
    };
    showCardModal.value = true;
};

const addCard = (columnId) => {
    if (!newCardTitle.value.trim()) return;
    
    router.post(route('boards.store-card', props.board.id), {
        column_id: columnId,
        title: newCardTitle.value,
        description: newCardDescription.value,
        user_id: newCardUserId.value,
        priority: newCardPriority.value,
        due_date: newCardDueDate.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newCardTitle.value = '';
            newCardDescription.value = '';
            newCardUserId.value = null;
            newCardPriority.value = 'medium';
            newCardDueDate.value = '';
            activeCardColumn.value = null;
        }
    });
};

const updateCard = () => {
    router.patch(route('boards.update-card', { id: props.board.id, card_id: selectedCard.value.id }), editForm.value, {
        preserveScroll: true,
        onSuccess: () => {
            showCardModal.value = false;
        }
    });
};

const addColumn = () => {
    if (!newColumnName.value.trim()) return;

    router.post(route('boards.store-column', props.board.id), {
        name: newColumnName.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            newColumnName.value = '';
            isAddingColumn.value = false;
        }
    });
};

// Sync columns when board prop updates
import { watch } from 'vue';
watch(() => props.board.columns, (newVal) => {
    columns.value = newVal;
}, { deep: true });
</script>

<template>
    <Head :title="board.name" />

    <AuthenticatedLayout>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ board.name }}
                </h2>
                <button v-if="isAdmin" 
                        @click="showMembersModal = true"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-2 px-4 rounded-lg transition-all shadow-lg shadow-indigo-500/20 active:scale-95 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    Members ({{ board.members.length }})
                </button>
            </div>

        <div class="py-6 h-[calc(100vh-140px)]">
            <div class="max-w-full mx-auto sm:px-6 lg:px-8 h-full">
                <div class="flex gap-4 h-full overflow-x-auto pb-4">
                    <div v-for="column in columns" :key="column.id" class="flex-shrink-0 w-80 bg-gray-100 dark:bg-gray-800 rounded-lg flex flex-col max-h-full">
                        <div class="p-3 flex justify-between items-center bg-gray-200 dark:bg-gray-900 rounded-t-lg">
                            <h3 class="font-bold text-gray-700 dark:text-gray-300">{{ column.name }}</h3>
                            <span class="text-xs bg-gray-300 dark:bg-gray-700 px-2 py-1 rounded text-gray-600 dark:text-gray-400">
                                {{ column.cards.length }} / {{ column.wip_limit || '∞' }}
                            </span>
                        </div>
                        <div class="p-2 flex-grow overflow-y-auto min-h-[50px]"
                             @dragover.prevent
                             @drop="moveCard($event.dataTransfer.getData('cardId'), column.id, column.cards.length)">
                            <div v-for="(card, index) in column.cards" :key="card.id" 
                                 draggable="true"
                                 @dragstart="$event.dataTransfer.setData('cardId', card.id)"
                                 @click="openCardModal(card)"
                                 class="bg-white dark:bg-gray-700 p-3 mb-2 rounded shadow-sm border-l-4 border-indigo-500 hover:shadow-md transition-shadow cursor-pointer">
                                <div class="flex justify-between items-start">
                                    <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200 leading-snug">{{ card.title }}</h4>
                                    <span v-if="card.priority" 
                                          :class="{
                                              'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300': card.priority === 'urgent',
                                              'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300': card.priority === 'high',
                                              'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300': card.priority === 'medium',
                                              'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300': card.priority === 'low',
                                          }"
                                          class="text-[10px] uppercase font-black px-1.5 py-0.5 rounded ml-2">
                                        {{ card.priority }}
                                    </span>
                                </div>
                                <p v-if="card.description" class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-2">
                                    {{ card.description }}
                                </p>
                                <div class="mt-3 flex justify-between items-center">
                                    <div v-show="card.assignee" class="flex items-center gap-1.5">
                                        <div class="w-5 h-5 rounded-full bg-indigo-500 flex items-center justify-center text-[10px] text-white font-bold">
                                            {{ card.assignee?.name.charAt(0) }}
                                        </div>
                                        <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">{{ card.assignee?.name }}</span>
                                    </div>
                                    <div v-if="card.due_date" class="text-[10px] text-gray-400 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 00-2 2z"></path></svg>
                                        {{ new Date(card.due_date).toLocaleDateString(undefined, { month: 'short', day: 'numeric' }) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-show="isAdmin && column.status === 'not_started'" class="p-2 border-t dark:border-gray-700">
                            <div v-if="activeToTodoCard" class="space-y-2">
                                <input v-model="newCardTitle" 
                                       @keyup.enter="addCard(column.id)"
                                       placeholder="Task title..."
                                       class="w-full text-xs p-2 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
                                       autofocus />
                                <div class="grid grid-cols-2 gap-2">
                                    <select v-model="newCardUserId" class="text-[10px] p-1.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option :value="null">Unassigned</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                    </select>
                                    <select v-model="newCardPriority" class="text-[10px] p-1.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <input type="date" v-model="newCardDueDate" class="w-full text-[10px] p-1.5 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200" />
                                <div class="flex gap-2 pt-1">
                                    <button @click="addCard(column.id)" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700 transition-colors font-semibold shadow-sm flex-grow">Create</button>
                                    <button @click="activeToTodoCard = false" class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 font-medium p-1">Cancel</button>
                                </div>
                            </div>
                            <button v-else 
                                    @click="activeToTodoCard = true"
                                    class="w-full text-left text-xs text-gray-500 hover:text-indigo-600 dark:hover:text-indigo-400 p-1 flex items-center gap-1 transition-colors">
                                <span class="text-lg leading-none">+</span> Add Task
                            </button>
                        </div>
                    </div>

                    <!-- Task Placement is automatic -->
                </div>
            </div>
        </div>
    </AuthenticatedLayout>

    <!-- Card Detail Modal -->
    <Teleport to="body">
        <div v-if="showCardModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showCardModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl transform transition-all overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-6">
                        <div class="flex-grow mr-4">
                            <input v-model="editForm.title" 
                                   :disabled="!isAdmin"
                                   class="text-2xl font-bold text-gray-900 dark:text-white leading-tight w-full bg-transparent border-none focus:ring-2 focus:ring-indigo-500 rounded p-1 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors disabled:hover:bg-transparent disabled:cursor-default" />
                            <div class="mt-2 flex items-center gap-3">
                                <span class="text-xs font-bold text-gray-400 uppercase tracking-tighter">In Column:</span>
                                <span class="text-xs font-semibold px-2 py-1 rounded bg-indigo-50 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300 shadow-sm border border-indigo-100 dark:border-indigo-800">
                                    {{ board.columns.find(c => c.id === selectedCard?.column_id)?.name }}
                                </span>
                            </div>
                        </div>
                        <button @click="showCardModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 shadow-sm">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Main Content -->
                        <div class="md:col-span-2 space-y-8">
                            <div>
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                    Description
                                </h4>
                                <textarea v-model="editForm.description"
                                          :disabled="!isAdmin"
                                          placeholder="Add a more detailed description..."
                                          class="w-full border-none bg-gray-50 dark:bg-gray-900/30 rounded-lg p-4 text-sm text-gray-700 dark:text-gray-300 min-h-[120px] focus:ring-2 focus:ring-indigo-500 transition-all placeholder-gray-400 disabled:opacity-75 disabled:cursor-not-allowed"></textarea>
                            </div>

                            <div>
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                    Dependencies
                                </h4>
                                <div class="grid grid-cols-1 gap-2 max-h-40 overflow-y-auto p-1 custom-scrollbar">
                                    <div v-for="col in board.columns" :key="'dep-col-'+col.id">
                                        <div v-for="c in col.cards" :key="'dep-card-'+c.id">
                                            <label v-if="c.id !== selectedCard.id" class="flex items-center gap-3 p-2 rounded hover:bg-gray-50 dark:hover:bg-gray-700/50 cursor-pointer transition-colors group">
                                                <input type="checkbox" :value="c.id" v-model="editForm.dependencies" :disabled="!isAdmin" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4 shadow-sm disabled:opacity-50" />
                                                <div class="flex flex-col">
                                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 group-hover:text-indigo-600 transition-colors">{{ c.title }}</span>
                                                    <span class="text-[9px] text-gray-400 uppercase font-black">{{ col.name }}</span>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar / Meta -->
                        <div class="space-y-6">
                            <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-100 dark:border-gray-800">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Properties</h4>
                                
                                <div class="space-y-5">
                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-0.5">Status</label>
                                        <select v-model="editForm.status" 
                                                :disabled="!canEdit"
                                                class="w-full text-xs p-2.5 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-75">
                                            <option value="not_started">Not Started</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                            <option value="blocked">Blocked</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-0.5">Assignee</label>
                                        <select v-model="editForm.user_id" 
                                                :disabled="!isAdmin"
                                                class="w-full text-xs p-2.5 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-75">
                                            <option :value="null">Unassigned</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-0.5">Priority</label>
                                        <select v-model="editForm.priority" 
                                                :disabled="!isAdmin"
                                                class="w-full text-xs p-2.5 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-75">
                                            <option value="low">Low</option>
                                            <option value="medium">Medium</option>
                                            <option value="high">High</option>
                                            <option value="urgent">Urgent</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-1.5 ml-0.5">Due Date</label>
                                        <input type="date" v-model="editForm.due_date" 
                                               :disabled="!isAdmin"
                                               class="w-full text-xs p-2.5 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-75" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 px-8 py-5 flex justify-end gap-3 border-t dark:border-gray-700">
                    <button @click="showCardModal = false" 
                            class="px-5 py-2.5 text-xs text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-bold transition-all uppercase tracking-widest">
                        Cancel
                    </button>
                    <button v-if="canEdit" @click="updateCard" 
                            class="px-6 py-2.5 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all font-bold shadow-lg shadow-indigo-500/20 active:scale-95 text-xs uppercase tracking-widest">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Members Management Modal -->
    <Teleport to="body">
        <div v-if="showMembersModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" @click.self="showMembersModal = false">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md transform transition-all overflow-hidden border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Board Members</h3>
                        <button @click="showMembersModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="space-y-4 max-h-60 overflow-y-auto mb-6 custom-scrollbar pr-2">
                        <div v-for="member in board.members" :key="member.id" class="flex justify-between items-center p-3 rounded-lg bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-indigo-500 flex items-center justify-center text-xs text-white font-bold">
                                    {{ member.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-800 dark:text-gray-200">{{ member.name }}</div>
                                    <div class="text-[10px] text-gray-500 dark:text-gray-400">{{ member.email }}</div>
                                </div>
                            </div>
                            <button v-if="member.email !== 'admin@example.com'" 
                                    @click="removeMember(member.id)"
                                    class="text-red-500 hover:text-red-700 p-2 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div class="border-t dark:border-gray-700 pt-6">
                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Invite New User</h4>
                        <div class="flex gap-2">
                            <select @change="(e) => inviteUser(e.target.value)" class="w-full text-sm p-2.5 rounded-lg border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Select a user to invite...</option>
                                <option v-for="user in users" 
                                        v-show="!board.members.find(m => m.id === user.id)" 
                                        :key="user.id" 
                                        :value="user.id">
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<style scoped>
/* Hidden scrollbar but functional */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}
.overflow-x-auto::-webkit-scrollbar-track {
    background: transparent;
}
.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #334155;
}
</style>
