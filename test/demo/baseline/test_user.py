import json
import requests
from requests.utils import requote_uri
import unittest
from ddt import data, ddt, file_data

import config
from util import util


@ddt
class User(unittest.TestCase):
    # Test data.
    id = None
    
    
    @classmethod
    def setUpClass(cls):
        util.login_admin()
    
        
    @classmethod
    def tearDownClass(cls):
        util.logout_admin()
        

    @file_data('test_user_add.json')
    def test_user_add(self, api, add_data):
        api = config.base_url + api
        resp = requests.post(api, data=json.dumps(add_data), headers=config.header)
        assert resp.status_code == requests.codes.ok, 'Fail to add user'
        json_obj = json.loads(resp.text)
        User.id = json_obj['data']['id']     
        assert User.id is not None, 'Fail to add user'
    
    
    @file_data('test_user_find.json')
    def test_user_find(self, api):
        api = config.base_url + api
        resp = requests.get(api, headers=config.header)
        assert resp.status_code == requests.codes.ok, 'Fail to query user'
        json_obj = json.loads(resp.text)
        assert len(json_obj['data']) > 0, 'Fail to query user'
        
    
    # @file_data('test_user.json')
    def test_user_get(self):
        api = config.base_url + '/api/users/' + User.id
        resp = requests.get(api, headers=config.header)
        assert resp.status_code == requests.codes.ok, 'Fail to get user info'
        json_obj = json.loads(resp.text)
        assert json_obj['data']['id'] == User.id, 'Fail to get user info'
            
        
    @file_data('test_user_put.json') 
    def test_user_put(self, api, put_data):
        api = f'{config.base_url}{api}/{User.id}'
        resp = requests.put(api, data=json.dumps(put_data), headers=config.header)
        assert resp.status_code == requests.codes.ok, 'Fail to update user'
        json_obj = json.loads(resp.text)
        assert json_obj['data']['remark'] == put_data['remark'], 'Fail to update user'
        
        
    # @file_data('test_user.json')
    def test_user_remove(self):
        api = config.base_url + '/api/users/' + User.id
        resp = requests.delete(api, headers=config.header)
        assert resp.status_code == requests.codes.ok, 'Fail to delete user'


if __name__ == '__main__':
    unittest.main()