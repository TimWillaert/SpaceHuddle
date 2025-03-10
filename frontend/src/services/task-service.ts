import {
  apiExecuteDelete,
  apiExecuteGetHandled,
  apiExecutePost,
  apiExecutePut,
} from '@/services/api';
import EndpointType from '@/types/enum/EndpointType';
import EndpointAuthorisationType from '@/types/enum/EndpointAuthorisationType';
import { Task, TaskForSaveAction } from '@/types/api/Task';

/* eslint-disable @typescript-eslint/no-explicit-any*/

export const getTaskById = async (
  taskId: string,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Task> => {
  return await apiExecuteGetHandled<Task>(
    `/${EndpointType.TASK}/${taskId}/`,
    {},
    authHeaderType
  );
};

export const deleteTask = async (id: string): Promise<boolean> => {
  return await apiExecuteDelete<any>(`/${EndpointType.TASK}/${id}/`);
};

export const getTaskList = async (
  topicId: string,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Task[]> => {
  const result = await apiExecuteGetHandled<Task[]>(
    `/${EndpointType.TOPIC}/${topicId}/${EndpointType.TASKS}/`,
    [],
    authHeaderType
  );
  if (result && Array.isArray(result)) return result;
  return [];
};

export const getDependentTaskList = async (
  taskId: string,
  authHeaderType = EndpointAuthorisationType.MODERATOR
): Promise<Task[]> => {
  return await apiExecuteGetHandled<Task[]>(
    `/${EndpointType.TASK}/${taskId}/${EndpointType.DEPENDENT}/`,
    [],
    authHeaderType
  );
};

export const postTask = async (
  topicId: string,
  data: Partial<TaskForSaveAction>
): Promise<Task> => {
  return await apiExecutePost<Task>(
    `/${EndpointType.TOPIC}/${topicId}/${EndpointType.TASK}/`,
    data
  );
};

export const putTask = async (
  data: Partial<TaskForSaveAction>
): Promise<Task> => {
  return await apiExecutePut<Task>(
    `/${EndpointType.TASK}/`,
    data,
    EndpointAuthorisationType.MODERATOR
  );
};
