<template>
  <div class="sidebar__page-header">
    <span class="toggleSidebarButton link" @click="toggleVisible">
      <font-awesome-icon :icon="displaySettings ? 'xmark' : 'bars'" />
    </span>
    <div class="sidebar__logo">
      <font-awesome-icon :icon="['fac', 'logoWithName']" class="logo" />
    </div>
    <span>
      <router-link to="/sessions">
        <font-awesome-icon icon="home" />
      </router-link>
      <router-link to="/profile">
        <font-awesome-icon icon="user" />
      </router-link>
    </span>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Prop, Watch } from 'vue-property-decorator';
import { EventType } from '@/types/enum/EventType';

@Options({
  components: {},
  emits: ['update:sidebarVisible'],
})
export default class SidebarHeader extends Vue {
  @Prop({ default: true }) readonly sidebarVisible!: boolean;
  displaySettings = false;

  toggleVisible(): void {
    const newVisibility = !this.displaySettings;
    this.$emit('update:sidebarVisible', newVisibility);
    this.eventBus.emit(EventType.CHANGE_SIDEBAR_VISIBILITY, newVisibility);
  }

  @Watch('sidebarVisible', { immediate: true })
  onSidebarVisibleChanged(): void {
    this.displaySettings = this.sidebarVisible;
  }
}
</script>

<style lang="scss" scoped>
.sidebar {
  &__page-header {
    display: flex;
    justify-content: space-between;
    width: 100%;

    span {
      display: inline-flex;
      align-items: center;

      a {
        color: white;
        font-size: 14px;
        margin-left: 0.6rem;
        margin-top: 0;
      }
    }
  }

  &__logo {
    font-size: 1.3rem;
  }
}
</style>
