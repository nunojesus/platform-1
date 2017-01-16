@extends('dashboard::layouts.dashboard')


@section('title','Журнал ошибок')
@section('description', $log->date )




@section('navbar')
    <div class="col-sm-6 col-xs-12 text-right">

        <ul class="nav navbar-nav navbar-right">

            <li>
                <a href="{{-- route('log-viewer::logs.download', [$log->date]) --}}" class="btn btn-link menu-save"><i class="ion-ios-download-outline fa fa-2x"></i></a>
            </li>

            <li>
                <a href="#delete-log-modal" class="btn btn-link " data-toggle="modal">
                    <i class="ion-ios-trash-outline fa fa-2x"></i>
                </a>
            </li>

        </ul>

    </div>
@stop



@section('content')



    <div class="hbox hbox-auto-xs hbox-auto-sm" id="menu-vue">




        <div class="col w-xxl bg-white-only b-r bg-auto no-border-xs">
            @include('dashboard::partials.logs.menu')
        </div>




        <!-- main content -->
        <div class="col">
            <section class="wrapper-md">


                <div class="bg-white-only bg-auto no-border-xs">


                    <div class="panel">

                        <div class="row wrapper">

                                <div class="panel panel-default">
                                    <div class="table-responsive">
                                        <table class="table table-condensed">
                                            <thead>
                                            <tr>
                                                <td>File path :</td>
                                                <td colspan="5">{{ $log->getPath() }}</td>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>Log entries :</td>
                                                <td>
                                                    <span class="label label-primary">{{ $entries->total() }}</span>
                                                </td>
                                                <td>Size :</td>
                                                <td>
                                                    <span class="label label-primary">{{ $log->size() }}</span>
                                                </td>
                                                <td>Created at :</td>
                                                <td>
                                                    <span class="label label-primary">{{ $log->createdAt() }}</span>
                                                </td>
                                                <td>Updated at :</td>
                                                <td>
                                                    <span class="label label-primary">{{ $log->updatedAt() }}</span>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="panel panel-default">

                                    <div class="table-responsive">
                                        <table id="entries" class="table table-condensed">
                                            <thead>
                                            <tr>
                                                <th width="10%">ENV</th>
                                                <th width="10%">Time</th>
                                                <th>Header</th>
                                                <th width="10%" class="text-right">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($entries as $key => $entry)
                                                <tr>
                                                    <td>
                                                <span class="label label-env text-dark">
                                                      <i class="{{$entry->level()}}"></i>
                                                    {{ $entry->env }}</span>
                                                    </td>

                                                    <td>{{ $entry->datetime->format('H:i:s') }}</td>
                                                    <td>
                                                        <p class="">{{ $entry->header }}</p>
                                                    </td>
                                                    <td class="text-right">
                                                        @if ($entry->hasStack())
                                                            <a class="btn btn-xs btn-default" role="button"
                                                               data-toggle="collapse" href="#log-stack-{{ $key }}"
                                                               aria-expanded="false" aria-controls="log-stack-{{ $key }}">
                                                                <i class="fa fa-toggle-on"></i> Stack
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if ($entry->hasStack())
                                                    <tr>
                                                        <td colspan="4" class="stack">
                                                            <pre class="stack-content collapse bg-black" id="log-stack-{{ $key }}">
                                                                {!! $entry->stack() !!}
                                                            </pre>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    @if ($entries->hasPages())
                                        <div class="panel-footer">
                                            {!! $entries->render() !!}

                                            <span class="label label-info pull-right">
                            Page {!! $entries->currentPage() !!} of {!! $entries->lastPage() !!}
                        </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

            </section>

            {{-- DELETE MODAL --}}
            <div id="delete-log-modal" class="modal fade">
                <div class="modal-dialog">
                    <form id="delete-log-form" action="{{-- route('log-viewer::logs.delete') --}}" method="POST">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="date" value="{{ $log->date }}">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <h4 class="modal-title">DELETE LOG FILE</h4>
                            </div>
                            <div class="modal-body">
                                <p>Are you sure you want to <span class="label label-danger">DELETE</span> this log file <span
                                            class="label label-primary">{{ $log->date }}</span> ?</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-default pull-left" data-dismiss="modal">Cancel
                                </button>
                                <button type="submit" class="btn btn-sm btn-danger" data-loading-text="Loading&hellip;">DELETE
                                    FILE
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>


        </div>





    <script>
        $(function () {
            var deleteLogModal = $('div#delete-log-modal'),
                deleteLogForm = $('form#delete-log-form'),
                submitBtn = deleteLogForm.find('button[type=submit]');

            deleteLogForm.on('submit', function (event) {
                event.preventDefault();
                submitBtn.button('loading');

                $.ajax({
                    url: $(this).attr('action'),
                    type: $(this).attr('method'),
                    dataType: 'json',
                    data: $(this).serialize(),
                    success: function (data) {
                        submitBtn.button('reset');
                        if (data.result === 'success') {
                            deleteLogModal.modal('hide');
                            location.replace("{{-- route('log-viewer::logs.list') --}}");
                        }
                        else {
                            alert('OOPS ! This is a lack of coffee exception !')
                        }
                    },
                    error: function (xhr, textStatus, errorThrown) {
                        alert('AJAX ERROR ! Check the console !');
                        console.error(errorThrown);
                        submitBtn.button('reset');
                    }
                });

                return false;
            });
        });
    </script>

@stop