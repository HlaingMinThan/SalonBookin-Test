<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { CalendarDate, getLocalTimeZone, today } from '@internationalized/date';
import type { DateValue } from 'reka-ui';
import { Calendar } from '@/components/ui/calendar';
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
} from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import { store } from '@/actions/App/Http/Controllers/BookingController';
import { index as bookingsIndex } from '@/actions/App/Http/Controllers/BookingLookupController';
import { index as slotsApiIndex } from '@/actions/App/Http/Controllers/SlotApiController';

interface SlotItem {
    id: number;
    date: string;
    time: string;
    service_id: number;
    service_name: string;
}

const todayDate = today(getLocalTimeZone());
const selectedDate = ref<DateValue>(todayDate);
const slots = ref<SlotItem[]>([]);
const loading = ref(false);
const selectedSlot = ref<SlotItem | null>(null);
const showModal = ref(false);

const form = useForm({
    slot_id: 0,
    customer_name: '',
    customer_phone: '',
});

// Group slots by service
const slotsByService = computed(() => {
    const groups: Record<string, { serviceName: string; slots: SlotItem[] }> = {};
    for (const slot of slots.value) {
        if (!groups[slot.service_id]) {
            groups[slot.service_id] = { serviceName: slot.service_name, slots: [] };
        }
        groups[slot.service_id].slots.push(slot);
    }
    return Object.values(groups);
});

function dateToString(date: DateValue): string {
    return `${date.year}-${String(date.month).padStart(2, '0')}-${String(date.day).padStart(2, '0')}`;
}

function fetchSlots(date: DateValue) {
    loading.value = true;
    selectedSlot.value = null;

    fetch(slotsApiIndex.url({ query: { date: dateToString(date) } }))
        .then(r => r.json())
        .then((data: SlotItem[]) => {
            slots.value = data;
            loading.value = false;
        });
}

// Fetch on mount for today
fetchSlots(selectedDate.value);

// Refetch when date changes
watch(selectedDate, (date) => {
    if (date) fetchSlots(date);
});

function selectSlot(slot: SlotItem) {
    selectedSlot.value = slot;
    form.slot_id = slot.id;
    form.customer_name = '';
    form.customer_phone = '';
    form.clearErrors();
    showModal.value = true;
}

function formatTime(timeStr: string) {
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function formatSelectedDate() {
    const d = selectedDate.value;
    const date = new Date(d.year, d.month - 1, d.day);
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
}

function submitBooking() {
    form.post(store.url(), {
        onSuccess: () => {
            showModal.value = false;
        },
    });
}
</script>

<template>
    <Head title="Book an Appointment" />

    <div class="min-h-screen bg-background">
        <!-- Header -->
        <header class="border-b border-border/50 bg-card/50 backdrop-blur-sm">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-6 py-4">
                <h1 class="text-xl font-bold text-primary">SalonBooker</h1>
                <Link
                    :href="bookingsIndex.url()"
                    class="text-sm font-medium text-muted-foreground transition hover:text-primary"
                >
                    My Appointments
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-6 py-8">
            <div class="mb-8 text-center">
                <h2 class="text-3xl font-bold tracking-tight">Book Your Appointment</h2>
                <p class="mt-2 text-muted-foreground">Pick a date, choose your service and time.</p>
            </div>

            <div class="flex flex-col gap-8 lg:flex-row">
                <!-- Calendar -->
                <div class="flex justify-center lg:justify-start">
                    <Card class="w-fit">
                        <CardContent class="p-2">
                            <Calendar
                                v-model="selectedDate"
                                :min-value="todayDate"
                                class="rounded-md"
                            />
                        </CardContent>
                    </Card>
                </div>

                <!-- Available Services & Slots -->
                <div class="flex-1">
                    <h3 class="mb-4 text-lg font-semibold">
                        Available on {{ formatSelectedDate() }}
                    </h3>

                    <div v-if="loading" class="space-y-4">
                        <div v-for="i in 3" :key="i" class="h-24 animate-pulse rounded-lg bg-muted" />
                    </div>

                    <div v-else-if="slotsByService.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
                        No available slots on this date.
                    </div>

                    <div v-else class="space-y-6">
                        <Card v-for="group in slotsByService" :key="group.serviceName">
                            <CardHeader class="pb-3">
                                <CardTitle class="text-base">{{ group.serviceName }}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="flex flex-wrap gap-2">
                                    <button
                                        v-for="slot in group.slots"
                                        :key="slot.id"
                                        @click="selectSlot(slot)"
                                        class="rounded-lg border-2 border-border bg-card px-4 py-2 text-sm font-medium transition-all hover:border-primary hover:bg-primary/5"
                                    >
                                        {{ formatTime(slot.time) }}
                                    </button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </main>

        <!-- Booking Modal -->
        <Dialog v-model:open="showModal">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Complete Your Booking</DialogTitle>
                    <DialogDescription v-if="selectedSlot">
                        {{ selectedSlot.service_name }} on {{ formatSelectedDate() }} at {{ formatTime(selectedSlot.time) }}
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitBooking" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Label for="name">Name</Label>
                        <Input id="name" v-model="form.customer_name" placeholder="Your name" />
                        <InputError :message="form.errors.customer_name" />
                    </div>
                    <div class="flex flex-col gap-2">
                        <Label for="phone">Phone</Label>
                        <Input id="phone" v-model="form.customer_phone" placeholder="Your phone number" />
                        <InputError :message="form.errors.customer_phone" />
                    </div>
                    <InputError :message="form.errors.slot_id" />
                    <DialogFooter>
                        <Button type="submit" :disabled="form.processing" class="w-full">
                            Book Now
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
