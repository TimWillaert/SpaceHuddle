<template>
  <span
    ref="visibilityObserver"
    v-observe-visibility="visibilityChanged"
    :id="uuid"
    class="visibilityObserver"
  ></span>
  <el-popover
    :placement="placement"
    :width="width"
    trigger="manual"
    :visible="showStep"
  >
    <div class="text">
      {{ $t(`tutorial.${type}.${step}`) }}
    </div>
    <div class="link check" v-on:click="stepDone">
      <font-awesome-icon icon="check"></font-awesome-icon>
    </div>
    <template #reference>
      <slot></slot>
    </template>
  </el-popover>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Prop, Watch } from 'vue-property-decorator';
import { getTutorialSteps, addTutorialStep } from '@/services/auth-service';
import { Tutorial } from '@/types/api/Tutorial';
import { EventType } from '@/types/enum/EventType';
import { v4 as uuidv4 } from 'uuid';

const reservedTutorialSteps: {
  [key: string]: {
    tutorial: Tutorial;
    reservedBy: string;
    duplicate: string[];
  };
} = {};

@Options({
  components: {},
})

/* eslint-disable @typescript-eslint/no-explicit-any*/
export default class TutorialStep extends Vue {
  @Prop({ default: '' }) step!: string;
  @Prop({ default: '' }) type!: string;
  @Prop({ default: 0 }) order!: number;
  @Prop({ default: false }) readonly disableTutorial!: boolean;
  @Prop({ default: 0 }) minDuplicates!: number;
  @Prop({ default: false }) displayAllDuplicates!: boolean;
  @Prop({ default: 'top' }) placement!: string;
  @Prop({ default: 300 }) width!: number;
  activateTutorial = false;
  tutorialSteps: Tutorial[] = [];
  dbLoaded = false;
  uuid = uuidv4();
  reloadCount = 0;
  lastReloadNo = -1;
  isVisible = false;

  get stepKey(): string {
    return `${this.type}.${this.step}`;
  }

  get tutorialItem(): Tutorial {
    return {
      step: this.step,
      type: this.type,
      order: this.order,
    };
  }

  mounted(): void {
    getTutorialSteps().then((steps) => {
      this.tutorialSteps = steps;
      this.dbLoaded = true;
    });

    this.eventBus.on(EventType.CHANGE_TUTORIAL, async () => {
      this.reloadCount++;
    });

    const visibilityObserver = this.$refs.visibilityObserver as any;
    const domContent = visibilityObserver.nextSibling.nextSibling;
    if (visibilityObserver && domContent) {
      domContent.id = this.uuid;
      domContent.appendChild(visibilityObserver);
    }
  }

  visibilityChanged(
    isVisible: boolean,
    entry: IntersectionObserverEntry
  ): void {
    this.isVisible = isVisible;
  }

  @Watch('disableTutorial', { immediate: true, deep: true })
  // eslint-disable-next-line @typescript-eslint/explicit-module-boundary-types
  onDisableTutorialChanged(): void {
    if (!this.disableTutorial) {
      if (!reservedTutorialSteps[this.stepKey]) {
        reservedTutorialSteps[this.stepKey] = {
          tutorial: this.tutorialItem,
          reservedBy: this.uuid,
          duplicate: [],
        };
      } else {
        reservedTutorialSteps[this.stepKey].duplicate.push(this.uuid);
      }
    } else {
      this.removeFromReservationList();
    }
    setTimeout(() => {
      this.activateTutorial = !this.disableTutorial;
    }, 1000);
  }

  unmounted(): void {
    this.removeFromReservationList();
  }

  removeFromReservationList(): void {
    const reservation = Object.values(reservedTutorialSteps).find(
      (step) =>
        step.tutorial.type === this.type && step.tutorial.step === this.step
    );
    if (reservation) {
      const duplicateIndex = reservation.duplicate.indexOf(this.uuid);
      if (duplicateIndex >= 0) reservation.duplicate.splice(duplicateIndex, 1);
      else if (this.isReservedByUuid(this.uuid)) {
        if (reservation.duplicate.length > 0) {
          reservation.reservedBy = reservation.duplicate[0];
          reservation.duplicate.splice(0, 1);
        } else {
          reservation.reservedBy = '';
          delete reservedTutorialSteps[this.stepKey];
        }
      }
    }
  }

  isReservedByUuid(uuid: string): boolean {
    if (reservedTutorialSteps[this.stepKey]) {
      const dom = document.getElementById(
        reservedTutorialSteps[this.stepKey].reservedBy
      );
      if (!dom) {
        reservedTutorialSteps[this.stepKey].reservedBy = this.uuid;
      }
      return reservedTutorialSteps[this.stepKey].reservedBy === uuid;
    }
    return false;
  }

  showDuplicate(): boolean {
    if (reservedTutorialSteps[this.stepKey])
      return (
        reservedTutorialSteps[this.stepKey].duplicate.length >=
        this.minDuplicates
      );
    return true;
  }

  getPreviousOrder(): number[] {
    const previousOrders = Object.values(reservedTutorialSteps)
      .filter(
        (reservation) =>
          reservation.tutorial.type === this.type &&
          reservation.tutorial.order < this.order
      )
      .map((reservation) => reservation.tutorial.order)
      .sort(function (a, b) {
        return b - a;
      });

    if (previousOrders.length > 0) return previousOrders;
    return [];
  }

  getIncludeStep(): boolean {
    return !!this.tutorialSteps.find(
      (tutorial) => tutorial.step == this.step && tutorial.type == this.type
    );
  }

  calcShowStep(): boolean {
    const previousOrders = this.getPreviousOrder();
    const previousTutorialSteps = this.tutorialSteps.filter(
      (tutorial) =>
        tutorial.type == this.type && previousOrders.includes(tutorial.order)
    );
    const previousStepsDone =
      previousOrders.length === 0 ||
      previousTutorialSteps.length === previousOrders.length;
    const reload = this.reloadCount != this.lastReloadNo;
    this.lastReloadNo = this.reloadCount;

    return (
      this.isVisible &&
      (reload || this.dbLoaded) &&
      !this.disableTutorial &&
      this.activateTutorial &&
      this.uuid.length > 0 &&
      (this.displayAllDuplicates || this.isReservedByUuid(this.uuid)) &&
      !this.getIncludeStep() &&
      previousStepsDone &&
      this.showDuplicate()
    );
  }

  get showStep(): boolean {
    return this.calcShowStep();
  }

  stepDone(): void {
    const item = this.tutorialItem;
    if (!this.getIncludeStep()) {
      addTutorialStep(item, this.eventBus);
    }
  }
}
</script>

<style lang="scss" scoped>
.text {
  word-break: break-word;
}

.check {
  color: var(--color-mint);
  width: 100%;
  display: inline-flex;
  justify-content: right;
}

.visibilityObserver {
  position: absolute;
  background-color: transparent;
}
</style>
