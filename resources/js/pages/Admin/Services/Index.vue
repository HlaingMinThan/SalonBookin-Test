<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import InputError from '@/components/InputError.vue';
import { store, update, destroy } from '@/actions/App/Http/Controllers/Admin/ServiceController';

interface Service {
    id: number;
    name: string;
}

const props = defineProps<{
    services: Service[];
}>();

const createForm = useForm({ name: '' });
const editForm = useForm({ name: '' });
const showCreate = ref(false);
const showEdit = ref(false);
const editingService = ref<Service | null>(null);

function submitCreate() {
    createForm.post(store.url(), {
        onSuccess: () => {
            createForm.reset();
            showCreate.value = false;
        },
    });
}

function openEdit(service: Service) {
    editingService.value = service;
    editForm.name = service.name;
    showEdit.value = true;
}

function submitEdit() {
    if (!editingService.value) return;
    editForm.put(update.url({ service: editingService.value.id }), {
        onSuccess: () => {
            showEdit.value = false;
            editingService.value = null;
        },
    });
}

function deleteService(service: Service) {
    if (!confirm(`Delete "${service.name}"?`)) return;
    router.delete(destroy.url({ service: service.id }));
}
</script>

<template>
    <Head title="Services" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Services</h1>

            <Dialog v-model:open="showCreate">
                <DialogTrigger as-child>
                    <Button>Add Service</Button>
                </DialogTrigger>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add Service</DialogTitle>
                        <DialogDescription>Create a new service for your salon.</DialogDescription>
                    </DialogHeader>
                    <form @submit.prevent="submitCreate" class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <Label for="create-name">Name</Label>
                            <Input id="create-name" v-model="createForm.name" placeholder="e.g. Haircut" />
                            <InputError :message="createForm.errors.name" />
                        </div>
                        <DialogFooter>
                            <Button type="submit" :disabled="createForm.processing">Create</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>

        <div v-if="services.length === 0" class="rounded-xl border border-dashed p-8 text-center text-muted-foreground">
            No services yet. Add your first service to get started.
        </div>

        <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="service in services" :key="service.id">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-lg font-medium">{{ service.name }}</CardTitle>
                </CardHeader>
                <CardContent class="flex gap-2">
                    <Button variant="outline" size="sm" @click="openEdit(service)">Edit</Button>
                    <Button variant="destructive" size="sm" @click="deleteService(service)">Delete</Button>
                </CardContent>
            </Card>
        </div>

        <Dialog v-model:open="showEdit">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Service</DialogTitle>
                    <DialogDescription>Update the service name.</DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-2">
                        <Label for="edit-name">Name</Label>
                        <Input id="edit-name" v-model="editForm.name" />
                        <InputError :message="editForm.errors.name" />
                    </div>
                    <DialogFooter>
                        <Button type="submit" :disabled="editForm.processing">Save</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
