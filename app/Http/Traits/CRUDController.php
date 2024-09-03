<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;

trait CRUDController
{
    public function all()
    {
        $this->repository->processCache = false;
        $data = $this->repository->list(['per_page' => 9999]);

        return view('dashboard/pages/'.$this->viewFolder.'/all', [
            $this->arrName => new $this->ListCollection($data),
        ]);
    }

    public function add()
    {
        return view('dashboard/pages/'.$this->viewFolder.'/new');
    }

    public function create(Request $request)
    {
        $data = $this->service->create($request->all());

        return redirect(route($this->rout_all));
    }

    public function edit($id)
    {
        $data = $this->repository->find($id);

        return view('dashboard/pages/'.$this->viewFolder.'/edit', [
            $this->singleName => new $this->Resource($data),
        ]);
    }

    public function update($id, Request $request)
    {

        $data = $this->repository->find($id);
        $this->service->update($data, $request->all());

        return redirect(route($this->rout_all));
    }

    public function delete($id)
    {
        $data = $this->repository->find($id);
        $this->repository->deleteModel($data);

        return redirect(route($this->rout_all));
    }
}
