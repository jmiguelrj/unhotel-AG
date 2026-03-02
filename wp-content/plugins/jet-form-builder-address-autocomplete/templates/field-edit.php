<template v-if="'<?= $this->get_id(); ?>' === currentItem.settings.type">
    <keep-alive>
        <jet-address-fields v-model="currentItem.settings.address_autocomplete"></jet-address-fields>
    </keep-alive>
</template>