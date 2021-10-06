import {
  apiExecuteDelete,
  apiExecuteGetHandled,
  apiExecutePost,
  apiExecutePut,
} from '@/services/api';
import EndpointType from '@/types/enum/EndpointType';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import { Idea } from '@/types/api/Idea';
import {OrderGroupList} from "@/types/api/OrderGroup";

/* eslint-disable @typescript-eslint/no-explicit-any*/

export const deleteIdea = async (id: string): Promise<boolean> => {
  return await apiExecuteDelete<any>(`/${EndpointType.IDEA}/${id}/`);
};

export const postIdea = async (
  taskId: string,
  data: Partial<Idea>
): Promise<Idea> => {
  return await apiExecutePost<Idea>(
    `/${EndpointType.TASK}/${taskId}/${EndpointType.IDEA}`,
    data,
    EndpointAuthorisationType.PARTICIPANT
  );
};

export const putIdea = async (
  id: string,
  data: Partial<Idea>
): Promise<Idea> => {
  data['id'] = id;
  return await apiExecutePut<Idea>(
    `/${EndpointType.IDEA}`,
    data,
    EndpointAuthorisationType.MODERATOR
  );
};

export const getIdeasForTask = async (
  taskId: string,
  orderType: string | null = null,
  refId: string | null = null,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Idea[]> => {
  let queryParameter = '';
  if (orderType) queryParameter = `?order=${orderType}`;
  if (refId && orderType) queryParameter = `${queryParameter}&refId=${refId}`;
  return await apiExecuteGetHandled<Idea[]>(
    `/${EndpointType.TASK}/${taskId}/${EndpointType.IDEAS}/${queryParameter}`,
    [],
    authHeaderType
  );
};

export const getIdeasForTopic = async (
  topicId: string,
  orderType: string | null = null,
  refId: string | null = null,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Idea[]> => {
  let queryParameter = '';
  if (orderType) queryParameter = `?order=${orderType}`;
  if (refId && orderType) queryParameter = `${queryParameter}&refId=${refId}`;
  return await apiExecuteGetHandled<Idea[]>(
    `/${EndpointType.TOPIC}/${topicId}/${EndpointType.IDEAS}/${queryParameter}`,
    [],
    authHeaderType
  );
};

export const getOrderGroups = async (
  taskId: string,
  orderType: string | null = null,
  refId: string | null = null,
  authHeaderType = EndpointAuthorisationType.MODERATOR,
  actualOrderGroupList: OrderGroupList = {},
  filter: (Idea) => boolean = (Idea) => {
    return true;
  }
): Promise<{ ideas: Idea[]; oderGroups: OrderGroupList }> => {
  const orderGroupList = {};
  let ideaList: Idea[] = [];
  await getIdeasForTask(taskId, orderType, refId, authHeaderType).then(
    (ideas) => {
      ideaList = ideas;
      ideas
        .filter((idea) => filter(idea))
        .forEach((ideaItem) => {
          if (ideaItem.order) {
            const orderGroup = orderGroupList[ideaItem.order];
            if (!orderGroup) {
              let displayCount = 3;
              if (ideaItem.order in actualOrderGroupList)
                displayCount =
                  actualOrderGroupList[ideaItem.order].displayCount;
              let color = null;
              if (ideaItem.category) color = ideaItem.category.parameter.color;
              orderGroupList[ideaItem.order] = {
                ideas: [ideaItem],
                avatar: ideaItem.avatar,
                color: color,
                displayCount: displayCount,
              };
            } else {
              orderGroup.ideas.push(ideaItem);
            }
          }
        });
    }
  );
  return { ideas: ideaList, oderGroups: orderGroupList };
};
