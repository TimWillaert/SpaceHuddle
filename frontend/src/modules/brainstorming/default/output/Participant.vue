<template>
  <ParticipantModuleDefaultContainer
    :task-id="taskId"
    :module="moduleName"
    id="PMDC"
  >
    <div id="preloader"></div>
    <div id="loadingScreen">
      <span
        >{{ $t('module.brainstorming.default.participant.loading') }}...</span
      >
      <span
        id="loading"
        v-loading="true"
        element-loading-background="rgba(0, 0, 0, 0)"
      ></span>
    </div>
    <div
      id="backgroundImage"
      v-on:click="
        showTextInput = false;
        showImgInput = false;
      "
    ></div>

    <div
      id="Platform"
      v-on:click="
        showTextInput = false;
        showImgInput = false;
      "
    ></div>
    <div id="RocketContainer" :class="{ rocketAnimateMove: doTakeoff }">
      <div
        id="Rocket"
        :class="{ rocketAnimateSprite: doTakeoff }"
        v-on:animationend="
          doTakeoff = false;
          showTextInput = true;
        "
        v-on:click="
          showTextInput = false;
          showImgInput = false;
        "
      ></div>

      <div id="rocketButtons">
        <button
          v-on:click="
            showTextInput = true;
            showImgInput = false;
          "
          class="window"
          :class="{ redWindow: !isFormValid() && !doTakeoff }"
          id="editText"
        ></button>
        <button
          v-on:click="
            showImgInput = true;
            showTextInput = false;
          "
          class="window"
          id="Image"
        ></button>
        <el-button
          v-on:click="
            showHistory = true;
            showImgInput = false;
            showTextInput = false;
          "
          type="primary"
          class="window"
          id="Cargo"
        ></el-button>
      </div>
    </div>

    <ValidationForm
      :form-data="formData"
      :use-default-submit="false"
      v-on:submitDataValid="save"
      v-on:reset="reset"
    >
      <el-form-item
        prop="description"
        :rules="[
          defaultFormRules.ruleRequired,
          defaultFormRules.ruleToLong(MAX_DESCRIPTION_LENGTH),
        ]"
        id="textInsert"
        v-if="showTextInput && !showImgInput"
      >
        <el-input
          type="textarea"
          rows="4"
          v-model="formData.description"
          name="keywords"
          :placeholder="
            $t('module.brainstorming.default.participant.descriptionInfo')
          "
          v-on:keypress="submitOnEnter"
        />
        <span
          class="info"
          :class="{
            error: MAX_DESCRIPTION_LENGTH < formData.description.length,
          }"
        >
          {{
            $t('module.brainstorming.default.participant.remainingCharacters')
          }}:
          {{ MAX_DESCRIPTION_LENGTH - formData.description.length }}
        </span>
      </el-form-item>
      <el-form-item
        v-if="showSecondInput && showTextInput && !showImgInput"
        prop="keywords"
        :rules="[
          defaultFormRules.ruleRequired,
          defaultFormRules.ruleToLong(MAX_KEYWORDS_LENGTH),
        ]"
        id="keywordInsert"
      >
        <el-input
          v-model="formData.keywords"
          name="keywords"
          :placeholder="
            $t('module.brainstorming.default.participant.keywordInfo')
          "
        />
        <span
          class="info"
          :class="{
            error: MAX_KEYWORDS_LENGTH < formData.keywords.length,
          }"
        >
          {{
            $t('module.brainstorming.default.participant.remainingCharacters')
          }}:
          {{ MAX_KEYWORDS_LENGTH - formData.keywords.length }}
        </span>
      </el-form-item>
      <el-form-item
        prop="imageWebLink"
        :rules="[defaultFormRules.ruleUrl]"
        id="imageInsert"
        v-if="showImgInput && !showTextInput"
      >
        <el-container>
          <el-aside width="8rem">
            <ImagePicker
              v-model:link="formData.imageWebLink"
              v-model:image="formData.imgDataUrl"
              :useEditOverlay="true"
            />
          </el-aside>
          <el-main></el-main>
        </el-container>
      </el-form-item>
      <el-form-item
        prop="stateMessage"
        :rules="[defaultFormRules.ruleStateMessage]"
        id="textErrorMessage"
      >
        <span class="media">
          <span class="media-content">
            <el-input
              v-model="formData.stateMessage"
              class="hide"
              input-style="display: none"
            />
          </span>
        </span>
      </el-form-item>

      <ImageUploader
        v-model:show-modal="showUploadDialog"
        v-model="base64ImageUrl"
      />

      <div id="LaunchButton" :class="{ disabled: !isFormValid() }">
        <el-button
          native-type="submit"
          id="LaunchButtonPressable"
          :class="{ disabled: !isFormValid() }"
          :disabled="!isFormValid()"
          v-on:click="
            doTakeoff = isFormValid();
            showImgInput = false;
            showTextInput = false;
          "
          >LAUNCH</el-button
        >
      </div>
    </ValidationForm>
    <el-drawer
      v-model="showHistory"
      :title="$t('module.brainstorming.default.participant.ideas')"
      size="70%"
    >
      <el-divider></el-divider>
      <div class="layout__columns">
        <IdeaCard
          v-for="idea in ideas"
          :key="idea.id"
          :idea="idea"
          :is-selectable="false"
          :is-editable="true"
          :show-state="false"
          :canChangeState="false"
          :authHeaderTyp="EndpointAuthorisationType.PARTICIPANT"
          @ideaDeleted="getTaskIdeas"
        >
        </IdeaCard>
      </div>
    </el-drawer>
  </ParticipantModuleDefaultContainer>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Prop, Watch } from 'vue-property-decorator';
import ParticipantModuleDefaultContainer from '@/components/participant/organisms/layout/ParticipantModuleDefaultContainer.vue';
import * as moduleService from '@/services/module-service';
import * as ideaService from '@/services/idea-service';
import { getSingleTranslatedErrorMessage } from '@/services/exception-service';
import { Module } from '@/types/api/Module';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import { ElMessage } from 'element-plus';
import ValidationForm, {
  ValidationFormCall,
} from '@/components/shared/molecules/ValidationForm.vue';
import { ValidationRuleDefinition, defaultFormRules } from '@/utils/formRules';
import { ValidationData } from '@/types/ui/ValidationRule';
import ImagePicker from '@/components/moderator/atoms/ImagePicker.vue';
import { submitOnEnter } from '@/types/ui/submit';
import {
  Idea,
  MAX_DESCRIPTION_LENGTH,
  MAX_KEYWORDS_LENGTH,
} from '@/types/api/Idea';
import IdeaSortOrder from '@/types/enum/IdeaSortOrder';
import IdeaCard from '@/components/moderator/organisms/cards/IdeaCard.vue';
import ImageUploader from '@/components/shared/organisms/ImageUploader.vue';

@Options({
  components: {
    IdeaCard,
    ParticipantModuleDefaultContainer,
    ValidationForm,
    ImagePicker,
    ImageUploader,
  },
})

/* eslint-disable @typescript-eslint/no-explicit-any*/
export default class Participant extends Vue {
  defaultFormRules: ValidationRuleDefinition = defaultFormRules;
  submitOnEnter = submitOnEnter;

  @Prop() readonly taskId!: string;
  @Prop() readonly moduleId!: string;
  @Prop({ default: false }) readonly useFullSize!: boolean;
  @Prop({ default: '' }) readonly backgroundClass!: string;
  module: Module | null = null;
  ideas: Idea[] = [];
  showHistory = false;
  base64ImageUrl: string | null = null;

  MAX_KEYWORDS_LENGTH = MAX_KEYWORDS_LENGTH;
  MAX_DESCRIPTION_LENGTH = MAX_DESCRIPTION_LENGTH;
  EndpointAuthorisationType = EndpointAuthorisationType;

  get moduleName(): string {
    if (this.module) return this.module.name;
    return '';
  }

  formData: ValidationData = {
    keywords: '',
    description: '',
    imageWebLink: null,
    imgDataUrl: null, // the datebase64 url of created image
  };

  showUploadDialog = false;
  showTextInput = true;
  showImgInput = false;

  doTakeoff = false;

  planets = [
    require('@/assets/illustrations/planets/brainstorming01.png'),
    require('@/assets/illustrations/planets/brainstorming02.png'),
    require('@/assets/illustrations/planets/brainstorming03.png'),
    require('@/assets/illustrations/planets/brainstorming04.png'),
  ];
  scalePlanet = false;

  waiting(): boolean {
    const element = document.getElementById('loadingScreen');
    if (element != null && !element.classList.contains('zeroOpacity')) {
      const preload = document.getElementById('preloader');
      preload?.classList.add('PreloadSprites');

      setTimeout(() => preload?.classList.remove('PreloadSprites'), 1000);
      setTimeout(() => element?.classList.add('zeroOpacity'), 1000);
      setTimeout(() => element?.classList.add('hidden'), 3000);
      return true;
    }
    return false;
  }

  @Watch('moduleId', { immediate: true })
  onModuleIdChanged(): void {
    this.getModule();
  }

  @Watch('taskId', { immediate: true })
  onTaskIdChanged(): void {
    this.getTaskIdeas();
  }

  mounted(): void {
    this.waiting();
  }

  async getModule(): Promise<void> {
    if (this.moduleId) {
      await moduleService
        .getModuleById(this.moduleId, EndpointAuthorisationType.PARTICIPANT)
        .then((module) => {
          this.module = module;
        });
    }
  }

  get keywordsEmpty(): boolean {
    return this.showSecondInput && this.formData.keywords.length <= 0;
  }

  isFormValid(): boolean {
    if (this.formData.description.length <= 0) {
      return false;
    } else if (this.formData.description.length > this.MAX_DESCRIPTION_LENGTH) {
      return false;
    } else if (this.keywordsEmpty) {
      return false;
    } else
      return !(
        this.showSecondInput &&
        this.formData.keywords.length > MAX_KEYWORDS_LENGTH
      );
  }

  reset(): void {
    this.formData.keywords = '';
    this.formData.description = '';
    this.formData.imageWebLink = null;
    this.formData.imgDataUrl = null;
    this.formData.call = ValidationFormCall.CLEAR_VALIDATE;
  }

  async save(): Promise<void> {
    ideaService
      .postIdea(this.taskId, {
        description:
          this.formData.keywords.length > 0 ? this.formData.description : '',
        keywords:
          this.formData.keywords.length > 0
            ? this.formData.keywords
            : this.formData.description,
        image: this.formData.imgDataUrl,
        link: this.formData.imageWebLink,
      })
      .then(
        (queryResult) => {
          this.reset();
          setTimeout(this.setNewPlanet, 500);
          ElMessage({
            message: this.$t('info.postIdea', [queryResult.keywords]),
            type: 'success',
            center: true,
            showClose: true,
          });
          this.ideas.splice(0, 0, queryResult);
        },
        (error) => {
          this.formData.stateMessage = getSingleTranslatedErrorMessage(error);
        }
      );
  }

  get showSecondInput(): boolean {
    return this.formData.description.length > MAX_KEYWORDS_LENGTH;
  }

  get activePlanetIndex(): number {
    if (this.ideas.length < this.planets.length) return this.ideas.length;
    return this.planets.length - 1;
  }

  setNewPlanet(): void {
    if (this.ideas.length >= this.planets.length) {
      this.scalePlanet = true;
      setTimeout(() => {
        this.scalePlanet = false;
      }, 1000);
    }
  }

  imageLinkChanged(): void {
    this.formData.imgDataUrl = null;
  }

  @Watch('base64ImageUrl', { immediate: true })
  onBase64ImageUrlChanged(): void {
    this.formData.imgDataUrl = this.base64ImageUrl;
    this.formData.imageWebLink = null;
  }

  deleteImage(): void {
    this.formData.imgDataUrl = null;
    this.formData.imageWebLink = null;
  }

  async getTaskIdeas(): Promise<void> {
    ideaService
      .getIdeasForTask(
        this.taskId,
        IdeaSortOrder.TIMESTAMP,
        null,
        EndpointAuthorisationType.PARTICIPANT
      )
      .then((queryResult) => {
        this.ideas = queryResult.filter((idea) => idea.isOwn).reverse();
      });
  }

  unhide(elementID: string): void {
    const targetElement = document.getElementById(elementID);
    if (targetElement !== null) {
      targetElement.className = 'unhidden';
    }
  }
}
</script>

<style lang="scss" scoped>
.el-button {
  border: unset;

  &.is-circle {
    padding: 0.4rem;

    &:focus,
    &:hover {
      background-color: unset;
    }
  }
}

.level-item {
  margin: 1.3rem auto;
  //margin-top: 0.5rem;
  //margin-bottom: 1rem;
}

.level.is-mobile .level-item:not(:last-child) {
  margin-bottom: auto;
}

.el-footer {
  text-align: right;
}

.layout__columns {
  padding: 1rem;
}

.el-badge::v-deep {
  .el-badge__content--primary {
    right: calc(1rem + var(--el-badge-size) / 2);
    top: calc(1rem + var(--el-badge-size) / 2);
    background-color: white;
    color: var(--color-mint-dark);
    font-weight: var(--font-weight-bold);
    font-size: 1rem;
  }
}

.el-divider--horizontal {
  background-color: var(--color-gray-inactive);
  margin: 0 1.5rem 1rem 1.5rem;
  width: calc(100% - 3rem);
}

ParticipantModuleDefaultContainer {
  overflow: hidden;
  color: var(--color-gray);
}

.zeroOpacity {
  opacity: 0 !important;
  transition: 2s;
}

.hidden {
  display: none !important;
}

div#loadingScreen {
  position: absolute;
  width: 100%;
  height: 100%;

  bottom: 0;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-color: var(--color-darkblue);
  z-index: 100;

  display: flex;
  justify-items: center;
  align-items: center;
  flex-direction: column;

  opacity: 1;
}

div#loadingScreen > span {
  width: 70%;
  text-align: center;
  color: white;
  font-size: var(--font-size-large);
  position: relative;
  margin: auto auto 0;
  left: 0;
  right: 0;
  top: 0;
  bottom: 0;
}

div#loadingScreen > span#loading {
  margin-top: 50px;
  margin-bottom: auto;
}

div#loadingScreen > span#loading::v-deep .path {
  stroke: white;
  stroke-width: 4;
}

div#cache {
  position: absolute;
  z-index: 1000;
  opacity: 1;
}

div#cache image {
  position: absolute;
}

#backgroundImage {
  position: absolute;
  width: 100%;
  height: 100%;

  top: 0;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-image: url('../../../../assets/illustrations/Form/Background.png');
  background-size: 100%;
  background-position: bottom;
  background-repeat: no-repeat;

  mask-image: url('../../../../assets/illustrations/Form/Mask.png');
  mask-size: contain;
  mask-repeat: repeat;
}

#Platform {
  position: absolute;
  width: 85%;
  height: 15%;
  top: 81%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-image: url('../../../../assets/illustrations/Form/Platform 1.png');
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

#Rocket {
  position: absolute;
  width: 100%;
  height: 100%;

  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-none.png');
  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

#RocketContainer {
  position: absolute;
  width: 70%;
  height: 75%;

  top: 25%;

  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;
}

@keyframes takeoffSprite {
  /*Sprite changes (couldn't find a way to loop keyframes within animation)*/
  0% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-none.png');
  }
  6% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-3.png');
  }
  7% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-3.png');
  }
  12% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-1.png');
  }
  18% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-1.png');
  }
  19% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-2.png');
  }
  24% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-2.png');
  }
  25% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-3.png');
  }
  30% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-3.png');
  }
  31% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }
  36% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }

  37% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  42% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  43% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  48% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  49% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }
  54% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }

  55% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  60% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  61% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  66% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  72% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }
  73% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }

  78% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  79% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  84% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  85% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  90% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }
  91% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }

  96% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  97% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
}
@keyframes takeoffMove {
  0% {
    top: 25%;
    left: 0.1%;
    right: 0;
  }
  2% {
    top: 25%;
    right: 0.2%;
    left: 0;
  }
  4% {
    top: 25%;
    left: 0.4%;
    right: 0;
  }
  6% {
    top: 25%;
    right: 0.6%;
    left: 0;
  }
  8% {
    top: 25%;
    left: 0.9%;
    right: 0;
  }
  10% {
    top: 25%;
    right: 1%;
    left: 0;
  }
  12% {
    top: 25%;
    left: 1%;
    right: 0;
  }
  14% {
    top: 25%;
    right: 1%;
    left: 0;
  }
  16% {
    top: 25%;
    left: 1%;
    right: 0;
  }
  18% {
    top: 25%;
    right: 1%;
    left: 0;
  }

  20% {
    left: 1%;
    right: 0;
  }
  22% {
    right: 1%;
    left: 0;
  }
  24% {
    left: 0.9%;
    right: 0;
  }
  26% {
    right: 0.8%;
    left: 0;
  }
  28% {
    left: 0.7%;
    right: 0;
  }
  30% {
    right: 0.6%;
    left: 0;
  }
  32% {
    left: 0.5%;
    right: 0;
  }
  34% {
    right: 0.5%;
    left: 0;
  }
  36% {
    left: 0.4%;
    right: 0;
  }
  38% {
    right: 0.4%;
    left: 0;
  }
  40% {
    left: 0.3%;
    right: 0;
  }
  42% {
    right: 0.3%;
    left: 0;
  }
  44% {
    left: 0.3%;
    right: 0;
  }
  46% {
    right: 0.2%;
    left: 0;
  }
  48% {
    left: 0.2%;
    right: 0;
  }
  50% {
    right: 0.2%;
    left: 0;
  }
  52% {
    left: 0.1%;
    right: 0;
  }
  54% {
    right: 0.1%;
    left: 0;
  }
  56% {
    left: 0;
    right: 0;
  }

  100% {
    top: -100%;
  }
}

@keyframes preloadSprites {
  /*Sprite changes (couldn't find a way to loop keyframes within animation)*/
  0% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-none.png');
  }
  10% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-1.png');
  }
  20% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-2.png');
  }
  30% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-3.png');
  }
  40% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-1.png');
  }
  50% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-2.png');
  }
  60% {
    background-image: url('../../../../assets/illustrations/Form/rocket/Rocket-fire-launch-3.png');
  }
  70% {
    background-image: url('../../../../assets/illustrations/Form/ButtonRed.png');
  }
}

.PreloadSprites {
  animation-name: preloadSprites;
  animation-duration: 0.5s;
  animation-iteration-count: 1;
}

.rocketAnimateSprite {
  animation-name: takeoffSprite;
  animation-duration: 5s;
  animation-timing-function: ease-in-out;
  animation-iteration-count: 2;
  animation-direction: alternate;
}

.rocketAnimateMove {
  animation-name: takeoffMove;
  animation-duration: 5s;
  animation-timing-function: ease-in-out;
  animation-iteration-count: 2;
  animation-direction: alternate;
}

button.window {
  position: absolute;
  width: 30%;
  height: 13%;

  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-color: transparent;
  border: none;

  background-size: contain;
  background-position: center;
  background-repeat: no-repeat;
}

button#editText {
  background-image: url('../../../../assets/illustrations/Form/Window 1.png');
  top: 14%;
}

button#Image {
  background-image: url('../../../../assets/illustrations/Form/Window 2.png');
  top: 33.5%;
}

button#Cargo {
  background-image: url('../../../../assets/illustrations/Form/Window 3.png');
  top: 53%;
}

button#editText.redWindow {
  background-image: url('../../../../assets/illustrations/Form/Window 1-error.png');
}

.el-form-item.hidden {
  display: none;
}

.el-form-item.unhidden {
  display: block;
}

.el-form-item#textInsert {
  position: absolute;
  width: 90%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  top: 34%;
}

.el-form-item#textErrorMessage::v-deep {
  .el-form-item__error {
    position: absolute;
    width: auto;
    left: 0;
    right: 0;
    margin-left: auto;
    margin-right: auto;

    top: 60%;

    text-align: center;
    padding: 4% 3%;
  }
}

.el-form-item#keywordInsert {
  position: absolute;
  width: 90%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;
  top: 50%;
}

#LaunchButton {
  position: absolute;
  width: 55%;
  height: 17%;

  top: 83%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  background-size: contain;
  background-position: right;
  background-repeat: no-repeat;
  background-image: url('../../../../assets/illustrations/Form/ButtonRed.png');
  transition: 0.2s;
}

#LaunchButton.disabled {
  background-image: url('../../../../assets/illustrations/Form/ButtonGrey.png');
  transition: 0.2s;
}

.el-button#LaunchButtonPressable {
  position: absolute;
  width: 65%;
  height: 27%;

  top: 7%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  font-size: var(--font-size-xlarge);
  font-weight: var(--font-weight-bold);
  color: #992a2a;

  background-color: #fe6e5d;
  border: 2px solid #992a2a;
  border-radius: 20px 0px 20px 20px;
  transition: 0.2s;
}

.el-button#LaunchButtonPressable.disabled {
  color: #475989;
  background-color: #c4ccde;
  border: 2px solid #475989;
  transition: 0.2s;
}

.el-form-item#imageInsert {
  position: absolute;

  top: 47%;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;

  justify-content: center;
}

.el-form-item#imageInsert .el-aside {
  position: absolute;
  left: 0;
  right: 0;
  margin-left: auto;
  margin-right: auto;
}

#preloader {
  position: fixed;
  width: 0;
  height: 0;
}

.info {
  width: auto;
  position: absolute;
  right: 0;
  top: -18px;
  padding: 0.5% 2%;
  background-color: var(--color-mint);
  border-radius: 20px;
  color: white;
}

.info.error {
  background-color: var(--color-red);
}

.el-form-item::v-deep {
  .el-form-item__error {
    width: auto;
    position: absolute;
    left: 0;

    padding: 1% 4%;
    background-color: var(--color-red);
    border-radius: 20px;
    color: white;
  }
}
</style>
