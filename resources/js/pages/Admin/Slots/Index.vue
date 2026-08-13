<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { store, destroy } from '@/actions/App/Http/Controllers/Admin/SlotController';

interface SlotItem {
    id: number;
    date: string;
    time: string;
    service: string;
    is_available: boolean;
}

interface ServiceItem {
    id: number;
    name: string;
}

const props = defineProps<{
    slots: SlotItem[];
    services: ServiceItem[];
}>();

const showCreate = ref(false);
const form = useForm({
    service_id: '',
    date: '',
    time: '',
});

function submitCreate() {
    form.post(store.url(), {
        onSuccess: () => {
            form.reset();
            showCreate.value = false;
        },
    });
}

function deleteSlot(slot: SlotItem) {
    if (!confirm('Delete this slot?')) return;
    router.delete(destroy.url({ slot: slot.id }));
}

function formatDate(dateStr: string) {
    const date = new Date(dateStr + 'T00:00:00');
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
}

function formatTime(timeStr: string) {
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

// Group slots by date
function groupedSlots() {
    const groups: Record<string, SlotItem[]> = {};
    for (const slot of props.slots) {
        if (!groups[slot.date]) groups[slot.date] = [];
        groups[slot.date].push(slot);
    }
    return groups;
}
</script>

<template>
    <Head title="Slots" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Time Slots</h1>

            <Dialog v-model:open="showCreate">
                <DialogTrigger as-child>
                    <Button>Add Slot</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Time Slot</DialogTitle>
                        <DialogDescription>Create a new available time slot.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitCreate" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label>Service</Label>
                            <Select v-model="form.service_id">
                                <SelectTrigger>
                                    <SelectValue placeholder="Select a service" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem v-for="s in services" :key="s.id" :value="String(s.id)">
                                        {{ s.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <InputError :message="form.errors.service_id" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label for="slot-date">Date</Label>
                            <Input id="slot-date" type="date" v-model="form.date" />
                            <InputError :message="form.errors.date" />
                        </div>
                        <div class="flex flex-col gap-2">
                            <Label for="slot-time">Time</Label>
                            <Input id="slot-time" type="time" v-model="form.time" />
                            <InputError :message="form.errors.time" />
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="form.processing">Create</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="slots.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            No slots yet. Add your first time slot.
        </div>

        <div v-else class="flex flex-col gap-6">
            <div v-for="(dateSlots, date) in groupedSlots()" :key="date">
                <h2 class="mb-3 text-lg font-medium text-muted-foreground">{{ formatDate(date as string) }}</h2>
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    <Card v-for="slot in dateSlots" :key="slot.id">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle class="text-base font-medium">{{ formatTime(slot.time) }}</CardTitle>
                            <Badge :variant="slot.is_available ? 'default' : 'secondary'">
                                {{ slot.is_available ? 'Available' : 'Booked' }}
                            </Badge>
                        </CardHeader>
                        <CardContent class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">{{ slot.service }}</span>
                            <Button variant="destructive" size="sm" @click="deleteSlot(slot)">Delete</Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
