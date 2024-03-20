import request from '../utils/request';

export default class MenuService {
  getMenuList = params => request.get('sys/menu', { params });
  getOptionalParentMenu = () => request.get('sys/menu/optional');
  addOrUpdateMenu = data => data.id > 0 ? request.patch('sys/menu', data) : request.post('sys/menu', data);
  deleteMenu = id => request.delete(`sys/menu/${id}`);
}