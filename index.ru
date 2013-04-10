require 'scorched'
require 'erubis'

class App < Scorched::Controller

  def eruby(file, params = {})
    Erubis::Eruby.new(File.read(file)).result(params)
  end

  def cal(year, month)
    eruby("cal.eruby", {
        :month => month,
        :year => year
    })
  end

  get '/' do |year, month|
    cal(Date.today().year, Date.today().month)
  end

  get '/:year/:month' do |year, month|
    cal(year[1].to_i(), month[1].to_i())
  end
end
run App