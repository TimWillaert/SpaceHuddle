<template>
  <ModeratorNavigationLayout
    :currentRouteTitle="$t('moderator.molecule.navigation.profile')"
  >
    <template v-slot:content>
      <div class="profile__content full-height-header">
        <h1>{{ $t('moderator.view.profile.header') }}</h1>
        <p class="profile__email">
          {{ $t('moderator.view.profile.info') }} {{ email }}
        </p>
        <router-link to="change-password">
          <el-button type="info" class="static-width el-button--submit">
            {{ $t('moderator.view.profile.changePassword') }}
          </el-button>
        </router-link>
        <el-button
          type="info"
          class="static-width el-button--submit"
          @click="logout"
        >
          {{ $t('moderator.view.profile.submit') }}
        </el-button>
        <el-button
          type="info"
          class="static-width el-button--submit"
          v-on:click="deleteUser"
        >
          {{ $t('moderator.view.profile.delete') }}
        </el-button>
      </div>
    </template>
  </ModeratorNavigationLayout>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import * as authService from '@/services/auth-service';
import ModeratorNavigationLayout from '@/components/moderator/organisms/layout/ModeratorNavigationLayout.vue';
import * as userService from '@/services/user-service';

@Options({
  components: {
    ModeratorNavigationLayout,
  },
})
export default class ModeratorProfile extends Vue {
  email = '';

  mounted(): void {
    this.email = authService.getUserData() || '';
  }

  logout(): void {
    authService.removeAccessTokenModerator();
    this.$router.push({ name: 'home' });
  }

  deleteUser(): void {
    userService.deleteUser().then(() => {
      authService.removeAccessTokenModerator();
      this.$router.push({ name: 'home' });
    });
  }
}
</script>

<style lang="scss" scoped>
.profile {
  background-color: var(--color-background-gray);

  h1 {
    margin-top: 0;
  }

  &__content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-bottom: 3rem;
  }

  &__email {
    margin-bottom: 2rem;
  }
}
</style>
