import request from '../utils/request';

export default class AttachmentService {
  getAttachmentList = params => request.get('devops/attachment', { params });
}