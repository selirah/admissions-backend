<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    private $_document;
    private $_school;

    public function __construct(Document $document, School $school)
    {
        $this->_document = $document;
        $this->_school = $school;
    }

    // @route  GET api/v1/document
    // @desc   Get documents
    // @access Private
    public function getDocument(Request $request)
    {
        $schoolId = $request->get('school_id');
        $document = $this->_document->_getDocuments($schoolId);
        if (!$document) {
            return response()->json(null, 200);
        }
        return response()->json($document, 200);
    }

    // @route  POST api/v1/document/letter-head
    // @desc   Create Letter Head
    // @access Private
    public function createLetterHead(Request $request)
    {
        if (!$request->hasFile('letter_head')) {
            $response = [
                'message' => 'Letter head field is required'
            ];
            return response()->json($response, 400);
        }

        $letterHead = $request->file('letter_head');

        $extension = $letterHead->getClientOriginalExtension();

        $letterHeadName = date('YmdHis') . '.' . $extension;
        Storage::disk('public')->put('letter_head/' . $letterHeadName, File::get($letterHead));

        $letterHeadUrl = url('uploads/letter_head/' . $letterHeadName);

        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);
        $schoolId = $school->id;
        $docs = $this->_document->_getDocuments($schoolId);

        if ($docs) {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_head' => $letterHeadUrl,
                'updated_at' => Carbon::now()
            ];
            $this->_document->_updateDocumentsBySchool($schoolId, $documentsData);
        } else {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_head' => $letterHeadUrl,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $this->_document->_saveDocuments($documentsData);
        }
        $docs = $this->_document->_getDocuments($schoolId);
        return response()->json($docs, 201);
    }

    // @route  POST api/v1/document
    // @desc   Create documents
    // @access Private
    public function createDocument(Request $request)
    {

        if (!$request->hasFile('documents')) {
            $response = [
                'message' => 'Make sure you upload something'
            ];
            return response()->json($response, 201);
        }

        $documentsUrl = [];
        $documents = $request->file('documents');

        foreach ($documents as $file => $document) {
            $documentName = date('YmdHis') . '.' . $document->getClientOriginalExtension();
            Storage::disk('public')->put('docs/' . $documentName, File::get($document));
            $documentsUrl[] = $documentName;
        }


        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);
        $schoolId = $school->id;

        $docs = $this->_document->_getDocuments($schoolId);

        if ($docs) {
            $documentsData = [
                'school_id' => $schoolId,
                'docs' => implode(',', $documentsUrl),
                'updated_at' => Carbon::now()
            ];
            $this->_document->_updateDocumentsBySchool($schoolId, $documentsData);
        } else {
            $documentsData = [
                'school_id' => $schoolId,
                'docs' => implode(',', $documentsUrl),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $this->_document->_saveDocuments($documentsData);
        }
        $docs = $this->_document->_getDocuments($schoolId);
        return response()->json($docs, 201);
    }

    // @route  POST api/v1/document/letter-footer
    // @desc   Create Letter Footer
    // @access Private
    public function createLetterFooter(Request $request)
    {
        if (!$request->hasFile('letter_footer')) {
            $response = [
                'message' => 'Letter footer field is required'
            ];
            return response()->json($response, 400);
        }

        $letterFooter = $request->file('letter_footer');

        $extension = $letterFooter->getClientOriginalExtension();

        $letterFooterName = date('YmdHis') . '.' . $extension;
        Storage::disk('public')->put('letter_footer/' . $letterFooterName, File::get($letterFooter));

        $letterHeadUrl = url('uploads/letter_footer/' . $letterFooterName);

        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);
        $schoolId = $school->id;
        $docs = $this->_document->_getDocuments($schoolId);

        if ($docs) {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_footer' => $letterHeadUrl,
                'updated_at' => Carbon::now()
            ];
            $this->_document->_updateDocumentsBySchool($schoolId, $documentsData);
        } else {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_footer' => $letterHeadUrl,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $this->_document->_saveDocuments($documentsData);
        }
        $docs = $this->_document->_getDocuments($schoolId);
        return response()->json($docs, 201);
    }


    public function removeLetterFooter(Request $request)
    {
        $userId = $request->user()->id;
        $school = $this->_school->_getSchool($userId);
        $schoolId = $school->id;
        $docs = $this->_document->_getDocuments($schoolId);

        if ($docs) {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_footer' => null,
                'updated_at' => Carbon::now()
            ];
            $this->_document->_updateDocumentsBySchool($schoolId, $documentsData);
        } else {
            $documentsData = [
                'school_id' => $schoolId,
                'letter_footer' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            $this->_document->_saveDocuments($documentsData);
        }
        $docs = $this->_document->_getDocuments($schoolId);
        return response()->json($docs, 201);
    }
}
