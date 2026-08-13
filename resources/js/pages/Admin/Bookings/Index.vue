<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { approve, reject } from '@/actions/App/Http/Controllers/Admin/BookingController';

interface BookingItem {
    id: number;
    customer_name: string;
    customer_phone: string;
    status: string;
    date: string;
    time: string;
    service: string;
    created_at: string;
}

const props = defineProps<{
    bookings: BookingItem[];
}>();

function approveBooking(booking: BookingItem) {
    router.put(approve.url({ booking: booking.id }));
}

function rejectBooking(booking: BookingItem) {
    router.put(reject.url({ booking: booking.id }));
}

function formatDate(dateStr: string) {
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
}

function formatTime(timeStr: string) {
    const [hours, minutes] = timeStr.split(':');
    const h = parseInt(hours);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const h12 = h % 12 || 12;
    return `${h12}:${minutes} ${ampm}`;
}

function statusVariant(status: string) {
    switch (status) {
        case 'approved': return 'default' as const;
        case 'rejected': return 'destructive' as const;
        case 'cancelled': return 'secondary' as const;
        default: return 'outline' as const;
    }
}
</script>

<template>
    <Head title="Bookings" />

    <div class="flex flex-col gap-6 p-4">
        <h1 class="text-2xl font-semibold">Bookings</h1>

        <div v-if="bookings.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            No bookings yet.
        </div>

        <div v-else class="flex flex-col gap-3">
            <Card v-for="booking in bookings" :key="booking.id">
                <CardHeader class="flex flex-row items-start justify-between space-y-0 pb-2">
                    <div>
                        <CardTitle class="text-base font-medium">{{ booking.customer_name }}</CardTitle>
                        <p class="text-sm text-muted-foreground">{{ booking.customer_phone }}</p>
                    </div>
                    <Badge :variant="statusVariant(booking.status)" class="capitalize">
                        {{ booking.status }}
                    </Badge>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-wrap items-center gap-4 text-sm">
                        <span class="font-medium text-primary">{{ booking.service }}</span>
                        <span class="text-muted-foreground">{{ formatDate(booking.date) }}</span>
                        <span class="text-muted-foreground">{{ formatTime(booking.time) }}</span>
                    </div>
                    <div v-if="booking.status === 'pending'" class="mt-3 flex gap-2">
                        <Button size="sm" @click="approveBooking(booking)">Approve</Button>
                        <Button size="sm" variant="destructive" @click="rejectBooking(booking)">Reject</Button>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
