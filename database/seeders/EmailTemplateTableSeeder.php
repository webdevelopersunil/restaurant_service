<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class EmailTemplateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('email_templates')->delete();

        \DB::table('email_templates')->insert(array (
            array (
                'id' => 1,
                'name' => 'job_post_notify',
                'email_subject' => "New Job Post",
                'email_body' => "<h2>:welcome</h2><p>Congratulations! You have posted a new job<p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 2,
                'name' => 'applied_job',
                'email_subject' => "Job Applicant (Provider)",
                'email_body' => "<h2>:title</h2><p>:body</p><p>:thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 3,
                'name' => 'approved_decline_provider',
                'email_subject' => "Account has been :status",
                'email_body' => "Dear :name,<br/><br/>Your account has been :status.<br/><br/>Thank you",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 4,
                'name' => 'Job_Offer_Accepted_from_RO',
                'email_subject' => "Job Offer Status",
                'email_body' => "Dear :restaurant,<br/><br/>:name has been accepted your offer with job service :service.<br/><br/>Thank you",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 5,
                'name' => 'Job_Offer_Rejected_from_RO',
                'email_subject' => "Job Offer Status",
                'email_body' => "Dear :restaurant,<br/><br/>:name has been rejected your offer with service :service.<br/><br/>Thank you",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 6,
                'name' => 'thankyou_email_after_verify_account',
                'email_subject' => "2Top Tech email verification confirmation",
                'email_body' => "<p>Congratulations :name and Welcome!<p><p>Thankyou for verifying your email address. To complete your profile, please login to your account.</p><p>Make it a great day!</p><p>Thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 7,
                'name' => 'send_offer_to_provider',
                'email_subject' => "New job offer from :RO",
                'email_body' => "<p>Hi :name,<p><p>The Job (:job) you had applied for, has been accepted and the Restaurant has sent an offer.</p><p>Thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 8,
                'name' => 'provider_applying_job',
                'email_subject' => "New Job Application",
                'email_body' => "<p>Hi :ro,<p><p>You have a new Technician! :technician has applied for Job :job posted by you.</p><p>Please click here to view your Job Applications and book your Tech!</p><p>Thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 9,
                'name' => 'part_request_email',
                'email_subject' => "Part Request",
                'email_body' => "<p>Hi :admin,<p><p>Technician :name has requested for a part.</p><p> Part Request detail </p><p>Thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
            array (
                'id' => 10,
                'name' => 'bank_detail_updated',
                'email_subject' => "Bank Detail Updated",
                'email_body' => "<p>Hi :admin,<p><p>Technician :name has updated bank details.</p><p>Thankyou</p>",
                'created_at' => '2022-12-01 11:26:15',
                'updated_at' => '2022-12-01 11:26:15',
            ),
        ));
    }
}
