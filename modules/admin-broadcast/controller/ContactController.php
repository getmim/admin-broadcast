<?php
/**
 * ContactController
 * @package admin-broadcast
 * @version 0.0.1
 */

namespace AdminBroadcast\Controller;

use LibFormatter\Library\Formatter;
use LibForm\Library\Form;
use LibForm\Library\Combiner;
use LibPagination\Library\Paginator;
use Broadcast\Model\{
    BroadcastContact as BContact,
    BroadcastContactGroup as BCGroup
};

class ContactController extends \Admin\Controller
{
    private function getParams(string $title): array{
        return [
            '_meta' => [
                'title' => $title,
                'menus' => ['broadcast', 'contact']
            ],
            'subtitle' => $title,
            'pages' => null
        ];
    }

    public function editAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact)
            return $this->show404();

        $contact = (object)[];

        $id = $this->req->param->id;
        if($id){
            $contact = BContact::getOne(['id'=>$id]);
            if(!$contact)
                return $this->show404();
            $params = $this->getParams('Edit Broadcast Contact');
        }else{
            $params = $this->getParams('Create New Broadcast Contact');
        }

        $c_opts = [
            'group' => [null, null, 'format', 'all', 'name']
        ];
        $combiner = new Combiner($id, $c_opts, 'broadcast-contact');
        $contact  = $combiner->prepare($contact);

        $params['opts'] = $combiner->getOptions();

        $form   = new Form('admin-broadcast-contact.edit');
        $params['form']   = $form;

        if(!($valid = $form->validate($contact)) || !$form->csrfTest('noob'))
            return $this->resp('broadcast/contact/edit', $params);

        $valid = $combiner->finalize($valid);

        if($id){
            if(!BContact::set((array)$valid, ['id'=>$id]))
                deb(BContact::lastError());
        }else{
            $valid->user = $this->user->id;
            if(!($id = BContact::create((array)$valid)))
                deb(BContact::lastError());
        }

        $combiner->save($id, $this->user->id);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => $id ? 2 : 1,
            'type'   => 'broadcast-contact',
            'original' => $contact,
            'changes'  => $valid
        ]);

        $next = $this->router->to('adminBroadcastContact');
        $this->res->redirect($next);
    }

    public function indexAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact)
            return $this->show404();

        $cond = $pcond = [];
        if($q = $this->req->getQuery('q'))
            $pcond['q'] = $cond['q'] = $q;
        $cond['status'] = 1;

        list($page, $rpp) = $this->req->getPager(25, 50);

        $contacts = BContact::get($cond, $rpp, $page, ['name'=>true]) ?? [];
        if($contacts)
            $contacts = Formatter::formatMany('broadcast-contact', $contacts, ['user']);

        $params             = $this->getParams('Broadcast Contact');
        $params['contacts'] = $contacts;
        $params['form']     = new Form('admin-broadcast-contact.index');

        $params['form']->validate((object)$this->req->get());

        // pagination
        $params['total'] = $total = BContact::count($cond);
        if($total > $rpp){
            $params['pages'] = new Paginator(
                $this->router->to('adminBroadcastContact'),
                $total,
                $page,
                $rpp,
                10,
                $pcond
            );
        }

        $this->resp('broadcast/contact/index', $params);
    }

    public function removeAction(){
        if(!$this->user->isLogin())
            return $this->loginFirst(1);
        if(!$this->can_i->manage_broadcast_contact)
            return $this->show404();

        $id      = $this->req->param->id;
        $contact = BContact::getOne(['id'=>$id]);
        $next    = $this->router->to('adminBroadcastContact');
        $form    = new Form('admin-broadcast-contact.index');

        if(!$contact)
            return $this->show404();

        if(!$form->csrfTest('noob'))
            return $this->res->redirect($next);

        // add the log
        $this->addLog([
            'user'   => $this->user->id,
            'object' => $id,
            'parent' => 0,
            'method' => 3,
            'type'   => 'broadcast-contact',
            'original' => $contact,
            'changes'  => null
        ]);

        $new_contact = [
            'status' => 0,
            'phone'  => time() . '#' . $contact->phone
        ];

        BContact::set($new_contact, ['id'=>$id]);
        
        $this->res->redirect($next);
    }
}