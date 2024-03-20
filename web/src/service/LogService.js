import request from '../utils/request';

export default class LogService {
  getLoginLog = params => request.get('log/login', { params });
  getOperationLog = params => request.get('log/operation', { params });
}