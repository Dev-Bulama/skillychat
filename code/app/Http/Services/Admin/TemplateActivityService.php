<?php
namespace App\Http\Services\Admin;

use App\Models\AiTemplate;
use App\Models\TemplateUsage;
use Carbon\Carbon;

class TemplateActivityService
{


    /**
     * Get all template useages  statistics
     *
     * @return array
     */
    public function getReport(): array
    {
        $templates = AiTemplate::whereHas("templateUsages")->get();

        $textReportsQuery = TemplateUsage::with(['template', 'admin', 'user'])
            ->filter(['template:slug', 'user:username'])
            ->date()
            ->where(function ($query) {
                $query->where('type', 'text')->orWhereNull('type');
            });

        $imageReportsQuery = TemplateUsage::with(['template', 'admin', 'user'])
            ->filter(['template:slug', 'user:username'])
            ->date()
            ->where('type', 'image');

        $videoReportsQuery = TemplateUsage::with(['template', 'admin', 'user'])
            ->filter(['template:slug', 'user:username'])
            ->date()
            ->where('type','video');

        // Word summaries
        $word_summaries = [
            'total' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->sum('total_words')
            ),
            'this_year' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereYear('created_at', date('Y'))
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->sum('total_words'),
                1
            ),
            'this_month' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereMonth('created_at', date('M'))
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->sum('total_words'),
                0
            ),
            'this_week' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->sum('total_words'),
                0
            ),
            'today' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereDate('created_at', Carbon::today())
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->sum('total_words'),
                0
            ),
            'total_template_usages' => $templates->count(),
        ];

        // Image summaries
        $image_summaries = [
            'total' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->where('type', 'image')
                    ->sum('total_images')
            ),
            'this_year' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereYear('created_at', date('Y'))
                    ->where('type', 'image')
                    ->sum('total_images'),
                1
            ),
            'this_month' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereMonth('created_at', date('M'))
                    ->where('type', 'image')
                    ->sum('total_images'),
                0
            ),
            'this_week' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->where('type', 'image')
                    ->sum('total_images'),
                0
            ),
            'today' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereDate('created_at', Carbon::today())
                    ->where('type', 'image')
                    ->sum('total_images'),
                0
            ),
            'total_template_usages' => $templates->count(),
        ];


        // Video summaries
        $video_summaries = [
            'total' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->where('type', 'video')
                    ->sum('total_videos')
            ),
            'this_year' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereYear('created_at', date('Y'))
                    ->where('type', 'video')
                    ->sum('total_videos'),
                1
            ),
            'this_month' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereMonth('created_at', date('M'))
                    ->where('type', 'video')
                    ->sum('total_videos'),
                0
            ),
            'this_week' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->where('type', 'video')
                    ->sum('total_videos'),
                0
            ),
            'today' => truncate_price(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->whereDate('created_at', Carbon::today())
                    ->where('type', 'video')
                    ->sum('total_videos'),
                0
            ),
            'total_template_usages' => $templates->count(),
        ];

        return [
            'breadcrumbs' => ['Home' => 'admin.home', 'Templates Reports' => null],
            'title' => 'Templates Reports',
            'reports' => $textReportsQuery
                ->latest()
                ->paginate(paginateNumber())
                ->appends(request()->all()),
            'imageReports' => $imageReportsQuery
                ->latest()
                ->paginate(paginateNumber())
                ->appends(request()->all()),
            'videoReports' => $videoReportsQuery
                    ->latest()
                    ->paginate(paginateNumber())
                    ->appends(request()->all()),
            'templates'         => $templates,
            'word_summaries'    => $word_summaries,
            'image_summaries'   => $image_summaries,
            'video_summaries'   => $video_summaries,

            'graph_data' => sortByMonth(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->selectRaw("MONTHNAME(created_at) as months, sum(total_words) as total")
                    ->whereYear('created_at', date('Y'))
                    ->where(function ($query) {
                        $query->where('type', 'text')->orWhereNull('type');
                    })
                    ->groupBy('months')
                    ->pluck('total', 'months')
                    ->toArray()
            ),
            'image_graph_data' => sortByMonth(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->where('type', 'image')
                    ->selectRaw("MONTHNAME(created_at) as months, sum(total_images) as total")
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('months')
                    ->pluck('total', 'months')
                    ->toArray()
            ),
            'video_graph_data' => sortByMonth(
                TemplateUsage::filter(['template:slug', 'user:username'])
                    ->where('type', 'video')
                    ->selectRaw("MONTHNAME(created_at) as months, sum(total_videos) as total")
                    ->whereYear('created_at', date('Y'))
                    ->groupBy('months')
                    ->pluck('total', 'months')
                    ->toArray()
            ),
        ];
    }

    public function getReportdetails($id)
    {

        return [
            'breadcrumbs'   => ['Home' => 'admin.home', 'Templates Reports' => route('admin.template.report.list') , 'Template Report Details' => null],
            'title'         => 'Templates Reports',
            'report'        => TemplateUsage::findOrFail($id)
        ];

    }


    /**
     * Destroy a specific template report
     *
     * @param integer|string $id
     * @return boolean
     */
    public function destroy(int|string $id) : bool {
        $report  = TemplateUsage::where('id',$id)->firstOrfail();
        $report->delete();
        return true;
    }

}
