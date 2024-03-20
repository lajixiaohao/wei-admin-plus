import request from '../utils/request';

export default class AdminService {
  getAdminList = params => request.get('sys/admin', { params });
  addOrEditAdmin = data => data.id > 0 ? request.patch('sys/admin', data) : request.post('sys/admin', data);
  getChildrenOfAdmin = id => request.get(`sys/admin/view/${id}`);
  resetPassword = data => request.patch('sys/admin/password', data);
  deleteAdmin = id => request.delete(`sys/admin/${id}`);
}