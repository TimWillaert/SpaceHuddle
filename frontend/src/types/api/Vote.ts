import { Idea } from '@/types/api/Idea';
import { Hierarchy } from '@/types/api/Hierarchy';

export interface Vote {
  id: string;
  ideaId: string;
  participantId: string;
  rating: number;
  detailRating: number;
  timestamp: string;
}

export interface VoteResult {
  idea: Idea | Hierarchy;
  ratingSum: number;
  detailRatingSum: number;
  countParticipant: number;
}
