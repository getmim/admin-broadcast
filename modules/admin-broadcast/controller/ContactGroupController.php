<?php
/**
 * ContactGroupController
 * @package admin-broadcast
 * @version 0.0.1
 */

namespace AdminBroadcast\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibPagination\Library\Paginator;
use Broadcast\Model\{
    BroadcastContactGroup as BCGroup,
    BroadcastContactGroupChain as BCGChain
};

class ContactGroupController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['broadcast', 'contact-group']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact_group)
            return $this->show404();

        $group = (object)[];

        $id = $this->req->param->id;
        if($id){
            $group = BCGroup::getOne(['id'=>$id]);
            if(!$group)
                return $this->show404();
            $params = $this->getParams('Edit Broadcast Contact Group');
        }else{
            $params = $this->getParams('Create New Broadcast Contact Group');
        }

        $form                 = new Form('admin-broadcast-contact-group.edit');
        $params['form']       = $form;

        if(!($valid = $form->validate($group)) || !$form->csrfTest('noob'))
            return $this->resp('broadcast/contact/group/edit', $params);

        if($id){
            if(!BCGroup::set((array)$valid, ['id'=>$id]))
                deb(BCGroup::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!BCGroup::create((array)$valid))
                deb(BCGroup::lastError());
        }

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'broadcast-contact-group',
            'original' => $group,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminBroadcastGroup');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact_group)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $groups = BCGroup::get($cond, $rpp, $page, ['name'=>true]) ?? [];
        if($groups)
            $groups = Formatter::formatMany('broadcast-contact-group', $groups, ['user']);

        $params             = $this->getParams('Broadcast Contact Group');
        $params['groups']   = $groups;
        $params['form']     = new Form('admin-broadcast-contact-group.index');

        $params['form']->validate( (object)$this->req->get() );

        // pagination
        $params['total'] = $total = BCGroup::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminBroadcastGroup'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('broadcast/contact/group/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact_group)
            return $this->show404();

        $id     = $this->req->param->id;
        $group  = BCGroup::getOne(['id'=>$id]);
        $next   = $this->router->to('adminBroadcastGroup');
        $form   = new Form('admin-broadcast-contact-group.index');

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'broadcast-contact-group',
            'original' => $group,
            'changes'  => null
        ]);

        BCGroup::remove(['id'=>$id]);
        BCGChain::remove(['group'=>$id]);
        
        $this->res->redirect($next);
    }
}