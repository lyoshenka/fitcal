require 'scorched'
require 'erubis'

class App < Scorched::Controller

  def eruby(file, params = {})
    Erubis::Eruby.new(File.read(file)).result(params)
  end

  def daysIn(MonthNum)
    (Date.new(Time.now.year,12,31).to_date<<(12-MonthNum)).day
  end

  get '/' do
    eruby("cal.eruby", {
        :daysInMonth => daysIn(6),
        :month => 'June',
        :year => 2013
    })
  end
end
run App