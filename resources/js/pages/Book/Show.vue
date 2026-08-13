<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { home } from '@/routes';
import { index as bookingsIndex } from '@/actions/App/Http/Controllers/BookingLookupController';

interface BookingData {
    id: number;
    customer_name: string;
    customer_phone: string;
    status: string;
    date: string;
    time: string;
    service: string;
}

const props = defineProps<{
    booking: BookingData;
}>();

function formatDate(dateStr: string) {
    const [year, month, day] = dateStr.split('-').map(Number);
    const date = new Date(year, month - 1, day);
    return date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' });
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
        case 'approved': return 'default';
        case 'rejected': return 'destructive';
        case 'cancelled': return 'secondary';
        default: return 'outline';
    }
}
</script>

<template>
    <Head title="Booking Confirmed" />

    <div class="min-h-screen bg-background">
        <header class="border-b border-border/50 bg-card/50 backdrop-blur-sm">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                <Link :href="home()" class="text-xl font-bold text-primary">SalonBooker</Link>
                <Link
                    :href="bookingsIndex.url()"
                    class="text-sm font-medium text-muted-foreground transition hover:text-primary"
                >
                    My Appointments
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-lg px-6 py-12">
            <div class="mb-8 text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-primary/10">
                    <svg class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h2 class="text-2xl font-bold">Booking Submitted!</h2>
                <p class="mt-2 text-muted-foreground">Your booking is awaiting confirmation from the salon.</p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center justify-between">
                        <span>Booking Details</span>
                        <Badge :variant="statusVariant(booking.status)" class="capitalize">
                            {{ booking.status }}
                        </Badge>
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Service</span>
                        <span class="font-medium">{{ booking.service }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Date</span>
                        <span class="font-medium">{{ formatDate(booking.date) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Time</span>
                        <span class="font-medium">{{ formatTime(booking.time) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Name</span>
                        <span class="font-medium">{{ booking.customer_name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Phone</span>
                        <span class="font-medium">{{ booking.customer_phone }}</span>
                    </div>
                </CardContent>
            </Card>

            <div class="mt-6 flex justify-center gap-4">
                <Button as-child variant="outline">
                    <Link :href="home()">Book Another</Link>
                </Button>
                <Button as-child>
                    <Link :href="bookingsIndex.url()">Check Appointments</Link>
                </Button>
            </div>
        </main>
    </div>
</template>
