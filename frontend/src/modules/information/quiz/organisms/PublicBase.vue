<template>
  <!-- PARTICIPANT -->
  <div v-if="showQuestion" :key="publicQuestion?.question.id" class="fill">
    <el-space
      direction="vertical"
      class="fill"
      v-if="
        publicQuestion &&
        publicQuestion.question &&
        (statePointer >= 0 || !isActive)
      "
    >
      <img
        v-if="publicQuestion.question.image"
        :src="publicQuestion.question.image"
        class="question-image"
        alt=""
      />
      <img
        v-if="publicQuestion.question.link && !publicQuestion.question.image"
        :src="publicQuestion.question.link"
        class="question-image"
        alt=""
      />
      <div class="question">
        {{ publicQuestion.question.keywords }}
      </div>
      <slot name="answers"></slot>
    </el-space>
  </div>

  <!-- PUBLIC SCREEN -->
  <template v-if="!showQuestion">
    <div v-if="publicQuestion" :key="publicQuestion?.question.id" class="question-container fadeToScore">
      <h1>{{ publicQuestion.question.order! + 1 }}. {{ publicQuestion.question.keywords }}</h1>
      <div class="countdown" v-bind:key="publicQuestion.question.id"></div>
    </div>

    <div v-else class="question-container fade-down anim-slow">
      <h1>{{ task?.name }}</h1>
    </div>

    <div v-if="!publicQuestion" class="timer fade-right anim-delay-xl anim-slow">
      <p v-if="task?.remainingTime && task.remainingTime > 0">Answer on your device!</p>
      <p v-else>Time's up!<br><span>Waiting for the moderator</span></p>
      <div v-if="task?.remainingTime && task.remainingTime > 0" class="timer__container">
        <div class="timer__container__icon">
          <img src="@/assets/icons/svg/clock.svg" alt="timer" >
        </div>
        <TimerProgress class="timer__container__text" :entity="task" :isQuiz="true"/>
      </div>
    </div>

    <div v-if="showStatistics && publicQuestion" v-bind:key="publicQuestion.question.id" class="fadeToScore">
      <div class="answers" v-if="publicQuestion.questionType === QuizQuestionTypes.MULTIPLECHOICE || publicQuestion.questionType === QuizQuestionTypes.SINGLECHOICE">
        <div v-for="answer in voteResult.sort((a, b) => {
          if(a.countParticipant < b.countParticipant) return 1
          if(a.countParticipant > b.countParticipant) return -1
          return 0
        })" v-bind:key="answer.idea.id" 
        :class="{
            correct: answer.idea.parameter.isCorrect,
            wrong: !answer.idea.parameter.isCorrect,
        }">
          <p>{{ answer.idea.keywords }}
            <br>
            <span>{{ voteResult.find(option => option.idea.id === answer.idea.id)?.countParticipant }} votes</span>
          </p>
        </div>
      </div>
      <div class="slider fade-right anim-slow" v-if="publicQuestion.questionType === QuizQuestionTypes.SLIDER">
        <p>{{ publicQuestion.question.parameter.minValue }}</p>
        <div id="slider">
          <div v-for="answer in voteResult" class="fade-down"
              :style="{ 'left': Math.floor(parseFloat(answer.idea.keywords) / (publicQuestion.question.parameter.minValue + publicQuestion.question.parameter.maxValue) * 100) + '%',
                        'transform': '-' + Math.floor(parseFloat(answer.idea.keywords) / (publicQuestion.question.parameter.minValue + publicQuestion.question.parameter.maxValue) * 100) + '%'}">
          </div>
          <p :style="{ 'left': Math.floor(parseFloat(publicQuestion.question.parameter.correctValue) / (publicQuestion.question.parameter.minValue + publicQuestion.question.parameter.maxValue) * 100 - 6) + '%',
                        'transform': '-' + Math.floor(parseFloat(publicQuestion.question.parameter.correctValue) / (publicQuestion.question.parameter.minValue + publicQuestion.question.parameter.maxValue) * 100 - 6) + '%'}"
              class="fade-down anim-slow"
          >
                        {{ publicQuestion.question.parameter.correctValue }}
          </p>
        </div>
        <p>{{ publicQuestion.question.parameter.maxValue }}</p>
      </div>
      <div class="order" v-if="publicQuestion.questionType === QuizQuestionTypes.ORDER">
        <draggable v-model="orderAnswers" 
                      tag="transition-group" 
                      :component-data="{ tag: 'ul', name: 'flip-list', type: 'transition' }"
                      v-bind="dragOptions"
                      group="orderAnswers"
                      item-key="id">
              <template #item="{ element }">
                <div class="orderDraggable">
                  <div>
                    <h2>{{ orderAnswers.findIndex((option) => option.id === element.id) + 1 }}</h2>
                    <p>{{ element.keywords }}</p>
                  </div>
                </div>
              </template>
            </draggable>
      </div>
      <div class="numbers" v-if="publicQuestion.questionType === QuizQuestionTypes.NUMBER">
        <div :class="{ 'numbers-correct': randomNumber === publicQuestion.question.parameter.correctValue  }">
          <p>{{ randomNumber }}</p>
        </div>
      </div>
    </div>

    <div v-bind:key="publicQuestion?.question.id" :class="{'fadeToScore': publicQuestion}" :style="{'position': 'absolute', 'width': '100%', 'height': '100%'}">
      <div v-for="participant of firstParticipants" class="spaceship anim-hover">
        <img src="@/assets/icons/svg/spaceship.svg" alt="space ship" >
        <p>{{ participant }}</p>
      </div>
    </div>

    <p class="participants fade-up anim-delay-2xl anim-slow">
      {{ task?.participantCount }} participants</p>

    <div v-if="showStatistics && publicQuestion" :key="publicQuestion?.question.id" id="scoreboard">
      <div class="scoreboard__participant">
        <h1>1</h1>
        <h2>{{ firstParticipants[0] }}</h2>
        <div :style="{maxWidth: '60%'}"></div>
        <img src="@/assets/icons/svg/spaceship.svg" alt="space ship" />
      </div>
      <div class="scoreboard__participant">
        <h1>2</h1>
        <h2>{{ firstParticipants[1] }}</h2>
        <div :style="{maxWidth: '40%'}"></div>
        <img src="@/assets/icons/svg/spaceship.svg" alt="space ship" />
      </div>
      <div class="scoreboard__participant">
        <h1>3</h1>
        <h2>{{ firstParticipants[2] }}</h2>
        <div :style="{maxWidth: '20%'}"></div>
        <img src="@/assets/icons/svg/spaceship.svg" alt="space ship" />
      </div>
    </div>
  </template>
</template>

<script lang="ts">
import { Options, Vue } from 'vue-class-component';
import { Prop, Watch } from 'vue-property-decorator';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import Vue3ChartJs from '@j-t-mcc/vue3-chartjs';
import { Task } from '@/types/api/Task';
import {
  getQuestionResultStorageFromHierarchy,
  getQuestionTypeFromHierarchy,
  Question,
  QuestionResultStorage,
  QuestionType,
QuizQuestionType,
} from '@/modules/information/quiz/types/Question';
import { VoteResult } from '@/types/api/Vote';
import {
  QuestionPhase,
  QuestionState,
  QuizStateProperty,
} from '@/modules/information/quiz/types/QuestionState';
import * as timerService from '@/services/timer-service';
import { Hierarchy } from '@/types/api/Hierarchy';
import * as taskService from '@/services/task-service';
import * as hierarchyService from '@/services/hierarchy-service';
import * as votingService from '@/services/voting-service';
import QuizResult from '@/modules/information/quiz/organisms/QuizResult.vue';
import {
  moduleNameValid,
  QuestionnaireType,
} from '@/modules/information/quiz/types/QuestionnaireType';
import TimerProgress from '@/components/shared/atoms/TimerProgress.vue';
import * as sessionService from '@/services/session-service';
import { ParticipantInfo } from '@/types/api/Participant';
import draggable from 'vuedraggable';

export interface PublicAnswerData {
  answer: Hierarchy;
  isHighlighted: boolean;
  isHighlightedTemporarily: boolean;
  isFinished: boolean;
}

@Options({
  components: {
    Vue3ChartJs,
    QuizResult,
    TimerProgress,
    draggable
  },
  emits: ['changePublicAnswers', 'changePublicQuestion', 'changeQuizState'],
})

/* eslint-disable @typescript-eslint/no-explicit-any*/
export default class PublicBase extends Vue {
  @Prop() readonly taskId!: string;
  @Prop({ default: true }) readonly usePublicQuestion!: boolean;
  @Prop({ default: -1 }) readonly activeQuestionIndex!: number;
  @Prop({ default: QuestionPhase.RESULT })
  readonly activeQuestionPhase!: QuestionPhase;
  @Prop({ default: EndpointAuthorisationType.MODERATOR })
  authHeaderTyp!: EndpointAuthorisationType;
  readonly intervalTime = 1000;
  interval!: any;
  task: Task | null = null;
  questions: Question[] = [];
  publicQuestion: Question | null = null;
  voteResult: VoteResult[] = [];
  questionnaireType: QuestionnaireType = QuestionnaireType.QUIZ;
  moderatedQuestionFlow = true;

  questionState: QuestionState = QuestionState.ACTIVE_CREATE_QUESTION;
  statePointer = 0;

  QuizQuestionTypes = QuestionType;
  QuestionType = QuestionnaireType;

  randomNumber = 0;
  numberInterval: any;
  numberIntervalTimer = 100;
  participants: ParticipantInfo[] = [];
  firstParticipants: string[] = [];

  orderAnswers: Hierarchy[] = []
  dragOptions = {
    animation: 200,
    group: "description",
    disabled: false,
    ghostClass: "ghost"
  }

  get isActive(): boolean {
    if (this.moderatedQuestionFlow) {
      if (this.task) return timerService.isActive(this.task);
    } else {
      return this.activeQuestionPhase === QuestionPhase.ANSWER;
    }
    return false;
  }

  get hasVotes(): boolean {
    return !!this.voteResult.find((vote) => vote.ratingSum > 0);
  }

  get showQuestion(): boolean {
    return (
      this.isActive ||
      (this.questionState === QuestionState.RESULT_ANSWER && this.hasVotes)
    );
  }

  get showExplanation(): boolean {
    return (
      this.questionState === QuestionState.RESULT_EXPLANATION && this.hasVotes
    );
  }

  get showStatistics(): boolean {
    return this.questionState === QuestionState.RESULT_STATISTICS;
  }

  get remainingTime(): number {
    const time = timerService.getRemainingTime(this.task);
    if (time) return time;
    return QuizStateProperty[QuestionState.ACTIVE_WAIT_FOR_VOTE].time;
  }

  get activeQuestionId(): string | null {
    if (
      this.task &&
      this.task.parameter &&
      (this.moderatedQuestionFlow || this.usePublicQuestion)
    ) {
      return this.task.parameter.activeQuestion;
    } else {
      const activeQuestion = this.activeQuestion;
      if (activeQuestion) return activeQuestion.id;
    }
    return '';
  }

  get activeQuestionType(): QuestionType {
    if (this.activeQuestion && this.activeQuestion.parameter.questionType) {
      return this.activeQuestion.parameter.questionType;
    }
    return QuestionType.MULTIPLECHOICE;
  }

  get activeQuestion(): Hierarchy | null {
    if (
      this.task &&
      this.task.parameter &&
      (this.moderatedQuestionFlow || this.usePublicQuestion)
    ) {
      const activeQuestionId = this.task.parameter.activeQuestion;
      const question = this.questions.find(
        (item) => item.question.id === activeQuestionId
      );
      if (question) return question.question;
    } else if (
      this.questions.length > this.activeQuestionIndex &&
      this.activeQuestionIndex >= 0
    ) {
      return this.questions[this.activeQuestionIndex].question;
    }
    return null;
  }

  get publicAnswers(): Hierarchy[] {
    if (this.publicQuestion) {
      if (this.questionState == QuestionState.ACTIVE_CREATE_QUESTION)
        if (this.statePointer > 0) {
          //return this.publicQuestion.answers.slice(0, this.statePointer - 1);
          return this.publicQuestion.answers;
        } else return [];
      return this.publicQuestion.answers;
    }
    return [];
  }

  get publicQuestionIndex(): number {
    if (this.publicQuestion)
      return this.questions.findIndex(
        (question) => question.question.id === this.publicQuestion?.question.id
      );
    return -1;
  }

  finishedAnswer(answer: Hierarchy): boolean {
    if (
      this.publicQuestion &&
      this.questionnaireType === QuestionnaireType.QUIZ
    ) {
      if (this.questionState == QuestionState.RESULT_ANSWER) {
        return true;
      }
    }
    return false;
  }

  highlightAnswer(answer: Hierarchy): boolean {
    if (
      this.publicQuestion &&
      this.questionnaireType === QuestionnaireType.QUIZ
    ) {
      if (this.questionState == QuestionState.RESULT_ANSWER) {
        return answer.parameter.isCorrect;
      }
    }
    return false;
  }

  highlightAnswerTemporarily(answer: Hierarchy): boolean {
    if (
      this.publicQuestion &&
      this.questionnaireType === QuestionnaireType.QUIZ
    ) {
      if (this.questionState == QuestionState.ACTIVE_LAST_CHANGE) {
        const index = this.publicQuestion.answers.indexOf(answer);
        return index === this.statePointer;
      }
    }
    return false;
  }

  @Watch('taskId', { immediate: true })
  async onTaskIdChanged(): Promise<void> {
    await this.getTask().then(() => {
      this.initQuestionState();
    });
    this.getHierarchies();
  }

  @Watch('publicQuestion.question.id', { immediate: true })
  async onPublicQuestionChanged(): Promise<void> {
    if(this.publicQuestion?.questionType === this.QuizQuestionTypes.NUMBER){
      this.randomNumber = 0;
      this.numberIntervalTimer = 100;
      this.generateNumber();
    } else if(this.publicQuestion?.questionType === this.QuizQuestionTypes.ORDER){
      this.orderAnswers = this.publicQuestion.answers;
      this.orderAnswers.sort((a, b) => 0.5 - Math.random());

      setTimeout(() => {
        this.orderAnswers = this.orderAnswers.sort((a, b) => a.order! - b.order!);
      }, 9000)
    }
  }

  @Watch('task.participantCount', { immediate: true })
  async onParticipantsChanged(): Promise<void> {
    if (this.task)
    {
      await sessionService.getParticipants(this.task!.sessionId).then((queryResult) => {
        this.participants = queryResult;
        this.getFirstParticipants();
      });
    }
  }

  private async getFirstParticipants(): Promise<void>{
    await votingService.getVotes(this.taskId).then((queryResult) => {
      if(this.firstParticipants.length < 6){
        let uniqueParticipants = [...new Set(queryResult.map(vote => vote.participantId))]
        uniqueParticipants.forEach((participantId) => {
          let nickname = this.participants.find(participant => participant.id === participantId)?.nickname
          if(nickname && !this.firstParticipants.includes(nickname)) this.firstParticipants.push(nickname)
        })
      }
    });
  }

  private generateNumber(): void {
    this.numberIntervalTimer = this.numberIntervalTimer * 1.05;
    this.numberInterval = setTimeout(() => {
        this.randomNumber = Math.floor(Math.random() * (this.publicQuestion?.question.parameter.maxValue - this.publicQuestion?.question.parameter.minValue + 1)) + this.publicQuestion?.question.parameter.minValue;
        
        if(this.publicQuestion?.questionType === this.QuizQuestionTypes.NUMBER && this.numberIntervalTimer < 500){
          this.generateNumber();
        } else{
          clearTimeout(this.numberInterval)
          this.randomNumber = this.publicQuestion?.question.parameter.correctValue
        }
    }, this.numberIntervalTimer);
  }

  private initQuestionState(): void {
    if (this.isActive) {
      this.questionState = QuestionState.ACTIVE_CREATE_QUESTION;
    } else {
      this.questionState =
        this.moderatedQuestionFlow &&
        this.questionState !== QuestionState.RESULT_STATISTICS
          ? QuestionState.RESULT_ANSWER
          : QuestionState.RESULT_STATISTICS;
    }
  }

  async getTask(): Promise<void> {
    await taskService
      .getTaskById(this.taskId, this.authHeaderTyp)
      .then((task) => {
        this.task = task;
        const module = task.modules.find((module) =>
          moduleNameValid(module.name)
        );
        if (module) {
          this.questionnaireType =
            QuestionnaireType[module.parameter.questionType.toUpperCase()];
          this.moderatedQuestionFlow = module.parameter.moderatedQuestionFlow;
        }
      });
  }

  async getHierarchies(): Promise<void> {
    if (this.taskId) {
      const activeQuestionId = this.activeQuestionId;
      await hierarchyService
        .getList(this.taskId, '{parentHierarchyId}', this.authHeaderTyp)
        .then(async (questions) => {
          const result: Question[] = [];
          let publicQuestion: Question | null = null;
          for (const index in questions) {
            const question = questions[index];
            const questionResultStorage: QuestionResultStorage =
              getQuestionResultStorageFromHierarchy(question);
            if (questionResultStorage === QuestionResultStorage.VOTING) {
              await hierarchyService
                .getList(this.taskId, question.id, this.authHeaderTyp)
                .then((answers) => {
                  const item: Question = {
                    questionType: getQuestionTypeFromHierarchy(question),
                    question: question,
                    answers: answers,
                  };
                  result.push(item);
                  if (question.id === activeQuestionId) {
                    publicQuestion = item;
                  }
                });
            } else {
              const item: Question = {
                questionType: getQuestionTypeFromHierarchy(question),
                question: question,
                answers: [],
              };
              result.push(item);
              if (question.id === activeQuestionId) {
                publicQuestion = item;
              }
            }
          }
          this.questions = result;
          if (this.moderatedQuestionFlow || this.usePublicQuestion) {
            this.publicQuestion = publicQuestion;
          } else if (
            this.activeQuestionIndex >= 0 &&
            this.activeQuestionIndex < this.questions.length
          ) {
            this.publicQuestion = this.questions[this.activeQuestionIndex];
          }
          await this.getVotes();
        });
    }
  }

  async getVotes(): Promise<void> {
    const activeQuestionId = this.activeQuestionId;
    if (activeQuestionId) {
      const questionResultStorage: QuestionResultStorage =
        getQuestionResultStorageFromHierarchy(this.activeQuestion);
      if (questionResultStorage === QuestionResultStorage.VOTING) {
        await votingService
          .getHierarchyResult(activeQuestionId, this.authHeaderTyp)
          .then((votes) => {
            this.voteResult = votes;
          });
      } else {
        await hierarchyService
          .getHierarchyResult(
            this.taskId,
            activeQuestionId,
            this.activeQuestion?.parameter.correctValue,
            this.authHeaderTyp
          )
          .then((votes) => {
            this.voteResult = votes;
          });
      }
    } else {
      await hierarchyService
        .getParentResult(this.taskId, this.authHeaderTyp)
        .then((votes) => {
          this.voteResult = votes;
        });
      //this.voteResult = [];
    }
  }

  @Watch('activeQuestionIndex', { immediate: true })
  onActiveQuestionIndexChanged(): void {
    this.checkState(true);
  }

  async checkState(staticUpdate = false): Promise<void> {
    const oldQuizState = this.questionState;
    const oldState = this.isActive;
    const oldQuestionId = this.activeQuestionId;
    await this.getTask();
    if (
      staticUpdate ||
      oldState !== this.isActive ||
      oldQuestionId !== this.activeQuestionId ||
      this.showStatistics
    ) {
      this.statePointer = -1;
      this.initQuestionState();
      await this.getHierarchies();
    }

    switch (this.questionState) {
      case QuestionState.ACTIVE_CREATE_QUESTION:
        this.statePointer++;
        if (
          this.publicQuestion &&
          this.statePointer > this.publicQuestion.answers.length
        ) {
          this.questionState = QuestionState.ACTIVE_WAIT_FOR_VOTE;
        }
        break;
      case QuestionState.ACTIVE_WAIT_FOR_VOTE:
        if (
          this.moderatedQuestionFlow &&
          this.remainingTime <
            QuizStateProperty[QuestionState.ACTIVE_WAIT_FOR_VOTE].time
        ) {
          this.questionState = QuestionState.ACTIVE_LAST_CHANGE;
          this.statePointer = 0;
        }
        break;
      case QuestionState.ACTIVE_LAST_CHANGE:
        if (this.publicQuestion) {
          const answerCount = this.publicQuestion.answers.length;
          if (answerCount > 0) {
            this.statePointer = (this.statePointer + 1) % answerCount;
          }
        }
        break;
      case QuestionState.DONE_THANKS:
        this.statePointer++;
        if (
          this.statePointer > QuizStateProperty[QuestionState.DONE_THANKS].time
        ) {
          this.questionState = QuestionState.DONE_WAIT;
        }
        break;
      case QuestionState.DONE_WAIT:
        break;
      case QuestionState.RESULT_ANSWER:
        this.statePointer++;
        if (
          this.statePointer >
          QuizStateProperty[QuestionState.RESULT_ANSWER].time
        ) {
          this.statePointer = 0;
          this.questionState = this.publicQuestion?.question.description
            ? QuestionState.RESULT_EXPLANATION
            : QuestionState.RESULT_STATISTICS;
        }
        break;
      case QuestionState.RESULT_EXPLANATION:
        this.statePointer++;
        if (
          this.statePointer >
          QuizStateProperty[QuestionState.RESULT_EXPLANATION].time
        ) {
          this.statePointer = 0;
          this.questionState = QuestionState.RESULT_STATISTICS;
        }
        break;
      case QuestionState.RESULT_STATISTICS:
        break;
    }

    if (oldQuizState !== this.questionState) {
      this.$emit('changeQuizState', this.questionState);
    }

    if (staticUpdate || oldQuestionId !== this.activeQuestionId) {
      this.$emit('changePublicQuestion', this.activeQuestion);
    }

    if (this.publicQuestion) {
      this.$emit(
        'changePublicAnswers',
        this.publicAnswers.map((answer) => {
          return {
            answer: answer,
            isHighlightedTemporarily: this.highlightAnswerTemporarily(answer),
            isHighlighted: this.highlightAnswer(answer),
            isFinished: this.finishedAnswer(answer),
          };
        })
      );
    }
    this.getVotes();
  }

  async mounted(): Promise<void> {
    this.startInterval();
  }

  startInterval(): void {
    this.interval = setInterval(this.checkState, this.intervalTime);
  }

  unmounted(): void {
    clearInterval(this.interval);
  }
}
</script>

<style lang="scss" scoped>
.el-space::v-deep {
  .el-space__item {
    width: 100%;
  }
}

.question {
  //border: 1px solid var(--color-primary);
  //border-radius: var(--border-radius);
  //padding: 1rem;
  font-weight: var(--font-weight-semibold);
  font-size: var(--font-size-xlarge);
  text-transform: uppercase;
  //text-align: center;
  color: var(--color-primary);
  margin: 1em 0;
}

.explanation {
  width: 100%;
  text-align: center;
  white-space: pre-line;
}

.result {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.el-steps {
  margin-bottom: 2rem;
}

.question-image {
  max-height: 30vh;
  object-fit: contain;
}

h1{
  font-weight: bold;
  font-size: 1.5rem;
}

.timer{
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;

  p{
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;

    span{
      font-size: 1rem;
      opacity: 0.7;
      font-weight: normal;
    }
  }

  &__container{
    display: flex;
    align-items: center;
    gap: 1rem;

    &__icon{
      background-color: white;
      width: fit-content;
      height: fit-content;
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 50%;
      padding: 0.8rem;

      img{
        height: 1.8rem;
      }
    }

    &__text{
      font-weight: bold;
      font-style: italic;
      font-size: 2.5rem;
      width: 7rem;
    }
  }
}

.participants{
  font-weight: 500;
  color: rgba(255,255,255,0.7);
}

.spaceship{
  position: absolute;

  img{
    width: 5rem;
    height: 5rem;

    position: absolute;
    bottom: 1.7rem;
    left: 50%;
    transform: translateX(-50%);
  }

  p{
    color: black;
    font-weight: 600;
    background-color: white;
    padding: 0.5rem;
    border-radius: 5px;
  }
}

.spaceship{
  &:nth-of-type(1){
    left: 4rem;
    top: 15%;
  }
  &:nth-of-type(2){
    right: 7rem;
    top: 30%;
  }
  &:nth-of-type(3){
    left: 12rem;
    top: 45%;
  }
  &:nth-of-type(4){
    right: 15rem;
    top: 60%;
  }
  &:nth-of-type(5){
    left: 7rem;
    top: 75%;
  }
  &:nth-of-type(6){
    right: 3rem;
    top: 85%;
  }
}

.answers{
  max-width: 50vw;
  display: flex;
  justify-content: center;
  gap: 1vw;
  flex-flow: wrap;

  div{
    width: 20vw;
    min-height: 5rem;
    font-size: 1.2rem;
    background-color: rgba(0,0,0,0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 10px;
    border: 1px solid rgba(255,255,255,0.1);
    text-align: center;

    span{
      opacity: 0.7;
      margin-top: 0.5rem;
      font-size: 0.9rem;
    }

    p{
      padding: 1rem;
    }

    opacity: 0;
    animation: fadeDown 1s ease forwards;
    &:first-of-type{
      animation-delay: 1s;
    }
    &:nth-of-type(2){
      animation-delay: 3s;
    }
    &:nth-of-type(3){
      animation-delay: 5s;
    }
    &:nth-of-type(4){
      animation-delay: 6s;
    }
    &:nth-of-type(5){
      animation-delay: 6.5s;
    }
    &:nth-of-type(6){
      animation-delay: 6.9s;
    }
    &:nth-of-type(7){
      animation-delay: 7.2s;
    }
    animation-delay: 8s;
  }

  .correct{
    opacity: 0;

    animation-name: fadeDown, rightAnswer;
    animation-duration: 1s, 4s;
    animation-timing-function: ease;
    animation-fill-mode: forwards;

    &:first-of-type{
      animation-delay: 1s, 8s;
    }
    &:nth-of-type(2){
      animation-delay: 3s, 8s;
    }
    &:nth-of-type(3){
      animation-delay: 5s, 8s;
    }
    &:nth-of-type(4){
      animation-delay: 6s, 8s;
    }
    &:nth-of-type(5){
      animation-delay: 6.5s, 8s;
    }
    &:nth-of-type(6){
      animation-delay: 6.9s, 8s;
    }
    &:nth-of-type(7){
      animation-delay: 7.2s, 8s;
    }
    animation-delay: 8s, 8s;
  }

  .wrong{
    opacity: 0;

    animation-name: fadeDown, wrongAnswer;
    animation-duration: 1s, 4s;
    animation-timing-function: ease;
    animation-fill-mode: forwards;

    &:first-of-type{
      animation-delay: 1s, 8s;
    }
    &:nth-of-type(2){
      animation-delay: 3s, 8s;
    }
    &:nth-of-type(3){
      animation-delay: 5s, 8s;
    }
    &:nth-of-type(4){
      animation-delay: 6s, 8s;
    }
    &:nth-of-type(5){
      animation-delay: 6.5s, 8s;
    }
    &:nth-of-type(6){
      animation-delay: 6.9s, 8s;
    }
    &:nth-of-type(7){
      animation-delay: 7.2s, 8s;
    }
    animation-delay: 8s, 8s;
  }
}

@keyframes fadeDown {
  0%{
    transform: translateY(-3rem);
    opacity: 0;
  }
  100%{
    transform: translateY(0);
    opacity: 1;
  }
}

@keyframes rightAnswer {
  0%{
    border-color: rgba(255,255,255,0.1);
    background-color: rgba(0,0,0,0.5);
  }
  100%{
    border-color: rgb(0, 222, 0);
    background-color: rgba(1, 94, 1, 0.393);
  }
}

@keyframes wrongAnswer {
  0%{
    opacity: 1;
  }
  100%{
    opacity: 0.6;
  }
}

.question-container{
  display: flex;
  flex-direction: column;
  align-items: center;

  .countdown{
    width: 30vw;
    height: 0.3rem;
    background-color: #01cf9e;
    margin-top: 1rem;
    border-radius: 10px;
    animation: countdown 8s 0.5s forwards ease-in;
  }
}

@keyframes countdown {
  0%{
    width: 30vw;
  }
  100%{
    width: 0;
  }
}

.slider{
  display: flex;
  align-items: center;
  width: 40vw;
  gap: 1.5rem;
  
  div{
    flex-grow: 1;
    background-color: #ffffff25;
    height: 1.5rem;
    border-radius: 10px;
  }

  p{
    font-size: 1.5rem;
    font-weight: bold;
    font-style: italic;
  }
}

#slider{
  position: relative;

  div{
    width: 4.5%;
    height: 100%;
    background: #01cf9fb3;
    position: absolute;
    
    @for $x from 1 through 27 {
      &:nth-child(#{$x}) {
        animation-delay: 300ms + 300ms * ($x - 1);
      }
    }

    animation-delay: 300ms * 26;
  }

  p{
    position: absolute;
    top: -5rem;
    padding: 1rem;
    border-radius: 50%;
    border: 1px solid rgb(0, 222, 0);
    background-color: rgba(1, 94, 1, 0.393);
    font-style: normal;
    font-weight: normal;
    animation-duration: 4s;
    animation-delay: 8s;
  }
}

.numbers div{
  width: 10vw;
  min-height: 5rem;
  font-size: 1.5rem;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  text-align: center;
}

.numbers-correct{
  animation: rightAnswer 4s ease forwards;
}

@keyframes fadeOut {
  0%{
    opacity: 1;
  }
  100%{
    opacity: 0;
  }
}

.fadeToScore{
  animation: fadeOut 1s 15s ease forwards;
}

#scoreboard{
  width: 100%;
  height: calc(100% - 2rem);
  position: absolute;
  opacity: 0;
  animation: fadeOut reverse 1s 16s forwards;

  .scoreboard__participant{
    display: flex;
    align-items: center;
    margin: 3rem 0;

    h1{
      width: 3rem;
      font-style: italic;
      opacity: 0.7;
      font-size: 1.7rem;
    }

    h2{
      width: 8rem;
      font-size: 1.7rem;
      margin-right: 2rem;
      font-weight: bold;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    div{
      animation: scoreWidth 6s 17s ease-in forwards;
      height: 0.2rem;
      width: 1rem;
      background-color: #FBB540;
      position: relative;

      &::before{
        content: '';
        width: 0.8rem;
        height: 0.8rem;
        position: absolute;
        left: -0.5rem;
        top: 50%;
        transform: translateY(-50%);
        background-color: #FBB540;
        border-radius: 50%;
      }

      &::after{
        content: '';
        width: 0.8rem;
        height: 0.8rem;
        position: absolute;
        right: -0.5rem;
        top: 50%;
        transform: translateY(-50%);
        background-color: #FBB540;
        border-radius: 50%;
      }
    }
  }

  img{
    height: 4.5rem;
    transform: rotate(90deg);
    margin-left: 2rem;
  }
}

@keyframes scoreWidth {
  0%{
    width: 1rem;
  }
  100%{
    width: 100%;
  }
}

.orderDraggable{
  width: 15vw;
  min-height: 4rem;
  font-size: 1.2rem;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.1);
  text-align: center;
  margin: 0.5rem 0;

  div{
    padding: 1rem;
    display: flex;
    width: 100%;
  }

  h2{
    width: 2rem;
    margin-right: 0.5rem;
    opacity: 0.7;
  }
}

.flip-list-move {
  transition: transform 0.5s;
}
.no-move {
  transition: transform 0s;
}
</style>
