import request from '../utils/request';

export default class RoleService {
  getRoleList = params => request.get('sys/role', { params });
  getChildrenOfRole = id => request.get(`sys/role/view/${id}`);
  addOrEditRole = data => data.id > 0 ? request.patch('sys/role', data) : request.post('sys/role', data);
  getAssignablePermission = id => request.get(`sys/role/permission/${id}`);
  submitPermission = data => request.post('sys/role/permission', data);
  deleteRole = id => request.delete(`sys/role/${id}`);
}