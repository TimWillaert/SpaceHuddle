<template>
  <div class="participant-background">
    <div
      class="participant-container join full-height centered-horizontal flex-column"
    >
      <main>
        <h1 class="heading heading--big heading--white">
          {{ $t('participant.view.join.header') }}
        </h1>
        <p class="join__text">
          {{ $t('participant.view.join.info') }}
        </p>
        <ValidationForm
          :form-data="formData"
          submit-label-key="participant.view.join.submit"
          v-on:submitDataValid="connectToSession"
        >
          <el-form-item prop="browserKey" v-if="browserKeyIsSet">
            <el-select v-model="formData.browserKey">
              <el-option
                v-for="info in recentlyUsedKeys"
                :key="info.connectionKey"
                :label="info.title"
                :value="info.connectionKey"
              >
              </el-option>
            </el-select>
            <p class="join__info">
              {{ $t('participant.view.join.browserKeyInfo') }}
            </p>
          </el-form-item>
          <el-form-item prop="connectionKey" :rules="validateRules">
            <el-input
              v-model="formData.connectionKey"
              name="connectionKey"
              autocomplete="on"
              :placeholder="$t('participant.view.join.pinInfo')"
            />
          </el-form-item>
          <!--add nickname entry here?-->
          <p class="join__text">
          {{ $t('participant.view.join.nicknameEntry') }}
          </p>
          <el-form-item prop="nickname">
            <el-input
              v-model="formData.nickname"
              name="nickname"
              :maxlength=16
              placeholder="Nickname"
            />
          </el-form-item>
        </ValidationForm>
      </main>
    </div>
  </div>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Participant, ConnectState } from '@/types/api/Participant';
import * as participantService from '@/services/participant-service';
import * as authService from '@/services/auth-service';
import { getSingleTranslatedErrorMessage } from '@/services/exception-service';
import ValidationForm from '@/components/shared/molecules/ValidationForm.vue';
import { ValidationRuleDefinition, defaultFormRules } from '@/utils/formRules';
import { ValidationData, ValidationRule } from '@/types/ui/ValidationRule';
import { Prop } from 'vue-property-decorator';
import * as sessionService from '@/services/session-service';
import { SessionInfo } from '@/types/api/Session';

@Options({
  components: {
    ValidationForm,
  },
})
export default class ParticipantJoin extends Vue {
  defaultFormRules: ValidationRuleDefinition = defaultFormRules;
  @Prop({ default: '' }) readonly connectionKey!: string;
  recentlyUsedKeys: SessionInfo[] = [];

  formData: ValidationData = {
    connectionKey: '',
    browserKey: '',
    nickname: ''
  };

  get validateRules(): ValidationRule[] {
    if (this.recentlyUsedKeys.length === 0)
      return [this.defaultFormRules.ruleRequired];
    return [];
  }

  mounted(): void {
    if (this.connectionKey) this.formData.connectionKey = this.connectionKey;

    const browserKeys = authService.getBrowserKeys();
    if (browserKeys.length > 0) {
      const connectionKeys = browserKeys.map((bKey) => bKey.split('.')[0]);
      sessionService.getSessionInfos(connectionKeys).then(
        (infos) => {
          if (infos && Array.isArray(infos)) {
            infos.forEach((info) => {
              const bKey = browserKeys.find((bKey) =>
                bKey.startsWith(info.connectionKey)
              );
              if (bKey) info.connectionKey = bKey;
            });
            this.recentlyUsedKeys = infos;
            const lastBrowserKey = authService.getLastBrowserKey();
            if (lastBrowserKey) this.formData.browserKey = lastBrowserKey;

            if (this.connectionKey) {
              const rejoinByUrlKey = infos.find((info) =>
                info.connectionKey.startsWith(this.connectionKey)
              );
              if (rejoinByUrlKey) {
                this.formData.browserKey = rejoinByUrlKey.connectionKey;
                this.formData.connectionKey = '';
              }
            }
          } else {
            this.setDefaultKey();
          }
        },
        () => {
          this.setDefaultKey();
        }
      );
    } else {
      this.setDefaultKey();
    }
  }

  setDefaultKey(): void {
    this.formData.connectionKey = authService.getLastBrowserKey();
    if (this.connectionKey) this.formData.connectionKey = this.connectionKey;
  }

  get browserKeyIsSet(): boolean {
    return this.recentlyUsedKeys.length > 0;
  }

  async connectToSession(): Promise<void> {
    let connectionKey = this.formData.connectionKey
      ? this.formData.connectionKey
      : this.formData.browserKey;
    if (!connectionKey.includes('.')) {
      const recentlyUsedKey = this.recentlyUsedKeys.find((info) =>
        info.connectionKey.startsWith(connectionKey)
      );
      if (recentlyUsedKey) {
        connectionKey = recentlyUsedKey.connectionKey;
      }
    }

    if (connectionKey.includes('.')) {
      participantService
        .reconnect(connectionKey)
        .then((queryResult) => {
          this.handleConnectionResult(queryResult);
        })
        .catch((error) => {
          this.formData.stateMessage = getSingleTranslatedErrorMessage(error);
        });
    } else {
      participantService.connect(connectionKey, this.formData.nickname.replace(/[^0-9a-z]/gi, '')).then(
        (queryResult) => {
          console.log(queryResult);
          this.handleConnectionResult(queryResult);
        })
        .catch((error) => {
          this.formData.stateMessage = getSingleTranslatedErrorMessage(error);
        });
    }
  }

  handleConnectionResult(
    participantData: Partial<Participant> | Participant
  ): boolean {
    if (participantData.participant && participantData.token) {
      if (participantData.participant.state === ConnectState.ACTIVE) {
        authService.setBrowserKey(
          participantData.participant.browserKey as string
        );
        authService.setAccessTokenParticipant(
          participantData.token.accessToken as string
        );
        console.log(participantData.participant.nickname);
        this.$router.push({
          name: 'participant-overview',
          params: {
                nickname: participantData.participant.nickname,
              },
        });
        return true;
      }
    }
    return false;
  }
}
</script>

<style lang="scss" scoped>
.join {
  padding: min(calc(var(--app-width) * 0.1), 4rem);
  padding-top: calc(var(--app-height) * 0.08);
  color: #fff;
  background: var(--color-darkblue);
  background-image: url('~@/assets/illustrations/telescope.png');
  background-position: center;
  background-size: cover;

  &__text {
    margin-bottom: 1rem;
  }

  &__info {
    text-align: center;
    margin: auto;
    line-height: 1rem;
    padding-top: 1rem;
  }
}

.el-select {
  width: 100%;
}
</style>
