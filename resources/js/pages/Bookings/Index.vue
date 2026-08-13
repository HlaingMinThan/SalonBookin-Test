<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { home } from '@/routes';
import { show } from '@/actions/App/Http/Controllers/BookingLookupController';

interface BookingItem {
    id: number;
    customer_name: string;
    status: string;
    date: string;
    time: string;
    service: string;
}

const props = defineProps<{
    bookings: BookingItem[];
    phone: string;
}>();

const form = useForm({
    phone: props.phone,
});

function lookUp() {
    form.post(show.url());
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
    <Head title="My Appointments" />

    <div class="min-h-screen bg-background">
        <header class="border-b border-border/50 bg-card/50 backdrop-blur-sm">
            <div class="mx-auto flex max-w-4xl items-center justify-between px-6 py-4">
                <Link :href="home()" class="text-xl font-bold text-primary">SalonBooker</Link>
                <Link
                    :href="home()"
                    class="text-sm font-medium text-muted-foreground transition hover:text-primary"
                >
                    Book Now
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-lg px-6 py-12">
            <h2 class="mb-6 text-center text-2xl font-bold">My Appointments</h2>

            <form @submit.prevent="lookUp" class="mb-8 flex gap-3">
                <div class="flex-1">
                    <Label for="phone" class="sr-only">Phone number</Label>
                    <Input id="phone" v-model="form.phone" placeholder="Enter your phone number" />
                    <InputError :message="form.errors.phone" class="mt-1" />
                </div>
                <Button type="submit" :disabled="form.processing">Look Up</Button>
            </form>

            <div v-if="phone && bookings.length === 0" class="rounded-lg border border-dashed p-6 text-center text-muted-foreground">
                No bookings found for this phone number.
            </div>

            <div v-else-if="bookings.length > 0" class="flex flex-col gap-3">
                <Card v-for="booking in bookings" :key="booking.id">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-base font-medium">{{ booking.service }}</CardTitle>
                        <Badge :variant="statusVariant(booking.status)" class="capitalize">
                            {{ booking.status }}
                        </Badge>
                    </CardHeader>
                    <CardContent>
                        <div class="flex gap-4 text-sm text-muted-foreground">
                            <span>{{ formatDate(booking.date) }}</span>
                            <span>{{ formatTime(booking.time) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </main>
    </div>
</template>
