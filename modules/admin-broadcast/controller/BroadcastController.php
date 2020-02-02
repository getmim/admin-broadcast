<?php
/**
 * BroadcastController
 * @package admin-broadcast
 * @version 0.0.1
 */

namespace AdminBroadcast\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibPagination\Library\Paginator;
use Broadcast\Model\{
    Broadcast,
    BroadcastItem as BItem,
    BroadcastContactGroup as BCGroup,
    BroadcastContactGroupChain as BCGChain
};
use Broadcast\Library\Broadcast as BCast;

class BroadcastController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['broadcast', 'all-cast']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast)
            return $this->show404();

        $broadcast        = (object)[];
        $params           = $this->getParams('Create New Broadcast');
        $form             = new Form('admin-broadcast.edit');
        $params['form']   = $form;
        $params['groups'] = [];
        
        $groups = BCGroup::get([], 0, 1, ['name'=>true]);
        if($groups)
            $params['groups'] = array_column($groups, 'name', 'id');

        if(!($valid = $form->validate($broadcast)) || !$form->csrfTest('noob'))
            return $this->resp('broadcast/edit', $params);

        $valid->total = BCGChain::count(['group'=>$valid->target]);
        $valid->user  = $this->user->id;

        if(!($id = Broadcast::create((array)$valid)))
            deb(Broadcast::lastError());

        BCast::created($id);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 1,
            'type'   => 'broadcast',
            'original' => $broadcast,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminBroadcast');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;
        if($status = $this->req->getQuery('status'))
            $pcond['status'] = $cond['status'] = $status;
        else
            $cond['status'] = ['__op', '>', 0];

        list($page, $rpp) = $this->req->getPager(25, 50);

        $broadcast = Broadcast::get($cond, $rpp, $page, ['time'=>false]) ?? [];
        if($broadcast)
            $broadcast = Formatter::formatMany('broadcast', $broadcast, ['user','target']);

        $params              = $this->getParams('Broadcast');
        $params['broadcast'] = $broadcast;
        $params['form']      = new Form('admin-broadcast.index');

        $params['form']->validate((object)$this->req->get());

        // pagination
        $params['total'] = $total = Broadcast::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminBroadcast'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('broadcast/index', $params);
    }

    public function itemAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast)
            return $this->show404();

        $id    = $this->req->param->id;
        $bcast = Broadcast::getOne(['id'=>$id]);
        if(!$bcast)
            return $this->show404();

        $params = $this->getParams('Broadcast Items');
        $params['broadcast'] = Formatter::format('broadcast', $bcast, ['user', 'target']);
        $params['items'] = [];

        $cond = [
            'broadcast' => $bcast->id
        ];

        list($page, $rpp) = $this->req->getPager(25, 50);

        $items = BItem::get($cond, $rpp, $page, ['status'=>false]);
        if($items)
            $params['items'] = Formatter::formatMany('broadcast-item', $items, ['contact']);

        $params['total'] = $total = BItem::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminBroadcastItem', ['id'=>$bcast->id]),
                $total,
                $page,
                $rpp,
                10
            );
        }

        $this->resp('broadcast/item', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast)
            return $this->show404();

        $id    = $this->req->param->id;
        $bcast = Broadcast::getOne(['id'=>$id]);
        $next  = $this->router->to('adminBroadcast');
        $form  = new Form('admin-broadcast.index');

        if(!$bcast)
            return $this->show404();

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'broadcast',
            'original' => $bcast,
            'changes'  => null
        ]);

        Broadcast::remove(['id'=>$id]);
        BItem::remove(['broadcast'=>$id]);

        $this->res->redirect($next);
    }
}